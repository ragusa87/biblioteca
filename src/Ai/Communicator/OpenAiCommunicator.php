<?php

namespace App\Ai\Communicator;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAiCommunicator extends AbstractCommunicator
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    private function sendRequest(string $url, array $data = [], string $method = 'GET'): string
    {
        $this->client = $this->client->withOptions(['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$this->aiModel->getToken()]]);

        $response = $this->client->request(
            $method,
            $url,
            [
                'json' => $data,
                'timeout' => 600,
            ]
        );

        $content = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // @phpstan-ignore-next-line
        $result = $content['choices'][0]['message']['content'];
        if (!is_string($result)) {
            throw new \RuntimeException('Failed to communicate with '.$this->aiModel);
        }

        return $result;
    }

    private function getPerplexityUrl(string $path): string
    {
        return "{$this->aiModel->getUrl()}{$path}";
    }

    #[\Override]
    public function interrogate(string $prompt): string
    {
        $params = [
            'model' => $this->aiModel->getModel(),
            'messages' => [
                ['role' => 'system', 'content' => $this->aiModel->getSystemPrompt()],
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        return $this->sendRequest($this->getPerplexityUrl('chat/completions'), $params, 'POST');
    }
}
