<?php

namespace App\Kobo\Har;

use Deviantintegral\Har\Cache;
use Deviantintegral\Har\Creator;
use Deviantintegral\Har\Entry;
use Deviantintegral\Har\Har;
use Deviantintegral\Har\Log;
use Deviantintegral\Har\Page;
use Deviantintegral\Har\PageTimings;
use Deviantintegral\Har\Request;
use Deviantintegral\Har\Response;
use Deviantintegral\Har\Serializer;
use Deviantintegral\Har\Timings;
use JMS\Serializer\Exception\RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Finder\Finder;

/**
 * Write HAR file with request/response/error information
 * Note: Please don't forget to cleanup files with the cleanup method
 */
class HarWriter
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    /**
     * @throws \JsonException
     */
    public function write(string $filename, RequestInterface|\Symfony\Component\HttpFoundation\Request $request, ResponseInterface|\Symfony\Component\HttpFoundation\Response $response = null, \Throwable $error = null): void
    {
        @mkdir(dirname($filename));
        $har = $this->getHar($filename);

        $entry = new Entry();

        // Date is mandatory
        $entry->setStartedDateTime(new \DateTime('10 seconds ago'));
        $entry->setRequest($this->convertRequest($request));

        // Fake timing, it's needed to have a valid har
        $entry->setTimings((new Timings())->setSend(10)->setWait(5)->setReceive(0));

        // Fake caching
        $entry->setCache(new Cache());

        // Put the response...
        if ($response !== null) {
            $entry->setResponse($this->convertResponse($response));
        }

        // Put page information, needed for valid HAR file
        $page = new Page();
        $page->setTitle($request->getUri());
        // Fake timings..
        $page->setStartedDateTime(new \DateTime('10 seconds ago'));
        $page->setPageTimings((new PageTimings())->setOnLoad(-1)->setOnContentLoad(-1));
        if ($error !== null) {
            $page->setComment($error->getMessage());
            $entry->setComment($error->getTraceAsString());
        }

        // Merge the page and entries from existing HAR file
        $har->getLog()->setEntries(array_merge($har->getLog()->getEntries(), [$entry]));
        $har->getLog()->setPages(array_merge($har->getLog()->getPages(), [$page]));

        $this->save($har, $filename);
    }

    protected function getHar(string $filename): Har
    {
        if (file_exists($filename)) {
            try {
                return (new Serializer())->deserializeHar((string) file_get_contents($filename));
            } catch (RuntimeException $e) {
                $this->logger->error(sprintf('Error reading HAR file %s: %s. Starting new..', $filename, $e->getMessage()));
            }
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
    public function save(Har $har, string $harLocation): void
    {
        $this->logger->debug(sprintf('Writing HAR file %s', $harLocation));

        $json = (new Serializer())->serializeHar($har);

        // Entries are encoded as \StdClass instead of array. This is a workaround.
        $object = json_decode($json, false, 512, JSON_THROW_ON_ERROR);

        // @phpstan-ignore-next-line
        $object->log->entries = ((array) $object->log->entries);
        $json = json_encode($object, JSON_PRETTY_PRINT);

        file_put_contents($harLocation, $json);
    }

    public function cleanup(string $location): void
    {
        $finder = new Finder();
        $finder
            ->ignoreVCS(true)
            ->files()
            ->name('*.har')
            ->date('< now - 12 hours')
            ->in($location);

        foreach ($finder as $file) {
            unlink($file->getRealPath());
        }
    }

    protected function convertRequest(RequestInterface|\Symfony\Component\HttpFoundation\Request $request): Request
    {
        if ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            $factory = new PsrHttpFactory();
            $request = $factory->createRequest($request);
        }

        return Request::fromPsr7Request($request);
    }

    protected function convertResponse(ResponseInterface|\Symfony\Component\HttpFoundation\Response $response): Response
    {
        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            $factory = new PsrHttpFactory();
            $response = $factory->createResponse($response);
        }

        return Response::fromPsr7Response($response);
    }
}
