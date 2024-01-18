<?php

namespace App\Kobo\Proxy;

use Deviantintegral\Har\Cache;
use Deviantintegral\Har\Creator;
use Deviantintegral\Har\Entry;
use Deviantintegral\Har\Har;
use Deviantintegral\Har\Log;
use Deviantintegral\Har\Page;
use Deviantintegral\Har\PageTimings;
use Deviantintegral\Har\Repository\HarFileRepository;
use Deviantintegral\Har\Request;
use Deviantintegral\Har\Response;
use Deviantintegral\Har\Timings;
use GuzzleHttp\Promise\Create;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

// TODO Transform to factory instead
class KoboProxyLogger
{
    use KoboHeaderFilterTrait;

    private string $harDirectory;

    public function __construct(protected LoggerInterface $logger, protected string $globalHarDirectory, protected string $accessToken)
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
        $this->currentRequestAsHarFile($request, $response);
    }

    /**
     * Goal is to write an HAR file that you can inspect later on
     * @param RequestInterface $request
     * @param ResponseInterface|null $response
     * @return void
     * @throws \JsonException
     */
    private function currentRequestAsHarFile(RequestInterface $request, ?ResponseInterface $response): void
    {
        $harName = sprintf('%s.har', date('Y-m-d'));
        $harLocation = $this->harDirectory.'/'.$harName;

        $har = $this->getHar($harName);

        $entry = new Entry();

        // Date is mandatory
        $entry->setStartedDateTime(new \DateTime('10 seconds ago'));
        $entry->setRequest(Request::fromPsr7Request($request));

        // Fake timing, it's needed to have a valid har
        $entry->setTimings((new Timings())->setSend(10)->setWait(5)->setReceive(0));

        // Fake caching
        $entry->setCache(new Cache());

        // Put the response...
        if ($response !== null) {
            $entry->setResponse(Response::fromPsr7Response($response));
        }

        // Put page information, needed for valid HAR file
        $page = new Page();
        $page->setTitle($request->getUri());
        // Fake timings..
        $page->setStartedDateTime(new \DateTime('10 seconds ago'));
        $page->setPageTimings((new PageTimings())->setOnLoad(-1)->setOnContentLoad(-1));

        // Merge the page and entries from existing HAR file
        $har->getLog()->setEntries(array_merge($har->getLog()->getEntries(), [$entry]));
        $har->getLog()->setPages(array_merge($har->getLog()->getPages(), [$page]));

        $this->save($har, $harLocation);
    }

    protected function getHar(string $harName): Har
    {
        $repository = new HarFileRepository($this->harDirectory);
        $harFile = $this->harDirectory.'/'.$harName;

        if (file_exists($harFile)) {
            return $repository->load($harName);
        }

        $har = new Har();
        $har->setLog(new Log());
        $har->getLog()->setVersion('1.2');
        $har->getLog()->setCreator((new Creator())->setName('KoboProxy'));
        $har->getLog()->setPages([]);

        $har->getLog()->setEntries([]);

        return $har;
    }

    /**
     * @throws \JsonException
     */
    private function save(Har $har, string $harLocation): void
    {
        $this->logger->debug(sprintf('Writing HAR file %s', $harLocation));

        $json = (new \Deviantintegral\Har\Serializer())->serializeHar($har);

        // Entries are encoded as \StdClass instead of array. This is a workaround.
        $object = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        // @phpstan-ignore-next-line
        $object->log->entries = ((array) $object->log->entries);
        $json = json_encode($object, JSON_PRETTY_PRINT);

        file_put_contents($harLocation, $json);

        $this->removeOldFiles();
    }

    private function removeOldFiles(): void
    {
        $finder = new Finder();
        $finder
            ->ignoreVCS(true)
            ->files()
            ->name('*.har')
            ->date('< now - 12 hours')
            ->in($this->globalHarDirectory);

        foreach ($finder as $file) {
            unlink($file->getRealPath());
        }
    }
}
