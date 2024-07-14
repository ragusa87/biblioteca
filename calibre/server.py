from flask import Flask, request, jsonify, send_file, make_response, render_template_string, redirect
from flask_cors import CORS
import os
import shutil
import subprocess
import sys
import uuid
app = Flask(__name__)
app.config['MAX_CONTENT_LENGTH'] = 4 * 1024 * 1024 * 1024 # 4GB max upload size

CORS(app)

# Static HTML form template
convert_form = '''
<!DOCTYPE html>
<html>
<head>
    <title>File Conversion Form</title>
</head>
<body>
    <h2>File Conversion Form</h2>
    <form action="/convert" method="post" enctype="multipart/form-data">
        <label for="file">Select file:</label>
        <input type="file" id="file" name="file"><br><br>
        <label for="input_format">Input Format:</label>
        <input type="text" id="input_format" name="input_format" value="pdf"><br><br>
        <label for="output_format">Output Format:</label>
        <input type="text" id="output_format" name="output_format" value="epub"><br><br>
        <input type="submit" value="Convert">
    </form>
</body>
</html>
'''

@app.route("/", methods=['GET'])
def index():
    return make_response(redirect("/convert"))

@app.route('/convert', methods=['POST', 'GET'])
def convert():
    if request.method == 'GET':
        # Render the HTML form with text/html content type
        response = make_response(render_template_string(convert_form))
        response.headers['Content-Type'] = 'text/html'
        return response

    data = request.files['file']
    unique_prefix = str(uuid.uuid4())
    input_format = request.form.get('input_format', 'pdf')
    output_format = request.form.get('output_format', 'epub')
    input_file = f"/tmp/pandoc/{unique_prefix}_input.{input_format}"
    output_file = f"/tmp/pandoc/{unique_prefix}_output.{output_format}"
    kobo_output_file = f"/tmp/pandoc/{unique_prefix}_kobo.epub"

    data.save(input_file)


    # Run ebook-convert conversion
    try:
        if output_format != input_format and not (output_format == 'kobo.epub' and input_format == 'epub'):
            # Run pandoc conversion with error handling
            subprocess.run(['ebook-convert', input_file, output_file], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        else:
            shutil.copy(input_file, output_file)
    except subprocess.CalledProcessError as e:
        # Handle pandoc conversion error
        os.remove(input_file)
        stderr_str = e.stderr.decode('utf-8')
        return jsonify({"error": "Conversion failed", "details": str(e), "stderr": stderr_str}), 500

    if 'kobo' in output_format.lower():
        try:
            # Run pandoc conversion with error handling
            subprocess.run(['kepubify', '-o', kobo_output_file, output_file ], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            os.remove(output_file)
            output_file = kobo_output_file
        except subprocess.CalledProcessError as e:
            # Handle pandoc conversion error
            os.remove(output_file)
            os.remove(input_file)
            stderr_str = e.stderr.decode('utf-8')
            return jsonify({"error": "Kobo Conversion failed", "details": str(e), "stderr": stderr_str}), 500

    response = send_file(output_file, as_attachment=True)

    # Remove the temporary files after sending
    os.remove(input_file)
    os.remove(output_file)

    return response

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "healthy"})

if __name__ == '__main__':
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 7654
    app.run(host='0.0.0.0', port=port)