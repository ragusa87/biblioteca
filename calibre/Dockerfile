#FROM alpine:edge
#RUN apk add --no-cache \
#    build-base \
#    python3 \
#    python3-dev
#RUN apk add calibre calibre-pyc --repository=http://dl-cdn.alpinelinux.org/alpine/edge/testing/


FROM ubuntu:oracular
RUN apt-get update && apt-get install -y \
    build-essential \
    libegl1 \
    libopengl0 \
    libxcb-cursor0 \
    python-is-python3 \
    python3 \
    python3-dev \
    python3-venv \
    wget \
    && rm -rf /var/lib/apt/lists/*
RUN wget -nv -O- https://download.calibre-ebook.com/linux-installer.py | python -c "import sys; main=lambda:sys.stderr.write('Download failed\n'); exec(sys.stdin.read()); main()"

RUN apt-get update && apt-get install -y \
    libxkbcommon-x11-0 \
    xserver-xorg-core \
    libnss3-dev \
    && rm -rf /var/lib/apt/lists/*
# Set up a virtual environment
ENV VIRTUAL_ENV=/opt/venv
RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"
ENV PATH="/opt/calibre:$PATH"

# Install python modules
RUN /opt/venv/bin/pip install --upgrade pip && \
    /opt/venv/bin/pip install \
flask \
flask_cors
# Copy the server script to the container
COPY --link server.py /usr/local/bin/server.py

## Install kepubify
ENV KEPUBIFY_VER="4.0.4"
RUN sh -c 'set -vx; if [ "$(uname -m)" = "x86_64" ];then kepubify_arch=64bit;elif [ "$(uname -m)" = "aarch64" ];then kepubify_arch=arm64;elif [ "$(uname -m)" = "armv7l" ];then kepubify_arch=arm; fi && wget https://github.com/pgaskin/kepubify/releases/download/v${KEPUBIFY_VER}/kepubify-linux-${kepubify_arch} -O /usr/local/bin/kepubify && chmod 755 /usr/local/bin/kepubify'

EXPOSE 7654
ENTRYPOINT ["/bin/sh"]

# Run the server
CMD ["-c", ". /opt/venv/bin/activate && exec python3 /usr/local/bin/server.py"]