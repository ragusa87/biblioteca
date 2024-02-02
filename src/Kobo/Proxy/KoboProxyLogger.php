<?php

namespace App\Kobo\Proxy;

use App\Kobo\Har\HarWriter;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class KoboProxyLogger
{
    use KoboHeaderFilterTrait;

    private string $harDirectory;

    public function __construct(protected KoboProxyConfiguration $configuration, protected LoggerInterface $logger, protected HarWriter $harWriter, protected string $globalHarDirectory, protected string $accessToken)
    {
        $this->harDirectory = $this->globalHarDirectory.'/'.$this->accessToken;

        if (!is_dir($this->harDirectory)) {
            mkdir($this->harDirectory, 0777, true);
        }
    }

    /**
     * Called when the middleware is handled by the client.
     *
     * @param callable $handler
     */
    public function __invoke(callable $handler): \Closure
    {
        return function ($request, array $options) use ($handler) {
            return $handler($request, $options)->then(
                $this->onSuccess($request),
                $this->onFailure($request)
            );
        };
    }

    /**
     * Returns a function which is handled when a request was successful.
     *
     * @param RequestInterface $request
     */
    protected function onSuccess(RequestInterface $request): \Closure
    {
        return function ($response) use ($request) {
            $this->log($request, $response);

            return $response;
        };
    }

    /**
     * Returns a function which is handled when a request was rejected.
     *
     * @param RequestInterface $request
     */
    protected function onFailure(RequestInterface $request): \Closure
    {
        return function ($reason) use ($request) {
            $this->log($request, null, $reason);

            return Create::rejectionFor($reason);
        };
    }

    private function log(RequestInterface $request, ResponseInterface $response = null, \Throwable $error = null): void
    {
        $this->logger->info(sprintf('Proxied: %s', (string) $request->getUri()), [
            'method' => $request->getMethod(),
            'status' => $response?->getStatusCode(),
        ]);

        if ($error !== null) {
            $this->logger->error('Proxy error: '.$error->getMessage(), ['exception' => $error]);

            return;
        }
        if ($this->configuration->isDebugHar()) {
            $this->saveVisitAsHarFile($request, $response);
        }
    }

    /**
     * Goal is to write an HAR file that you can inspect later on
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @return void
     */
    private function saveVisitAsHarFile(RequestInterface $request, ?ResponseInterface $response): void
    {
        $harName = sprintf('%s-proxy.har', date('Y-m-d'));
        $harLocation = $this->harDirectory.'/'.$harName;

        $this->harWriter->write($harLocation, $request, $response);
        $this->logger->debug(sprintf('HAR file written: %s', $harLocation));
    }
}
