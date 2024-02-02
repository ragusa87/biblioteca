<?php

namespace App\Kobo\Har;

use App\Kobo\Proxy\KoboProxyConfiguration;
use App\Security\KoboTokenExtractor;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Write every Kobo's request/response to a HAR file
 */
class KoboHarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly KoboProxyConfiguration $configuration,
        private readonly HarWriter $harWriter,
        private readonly KoboTokenExtractor $tokenExtractor,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $token = $this->tokenExtractor->extractAccessToken($event->getRequest());
        if ($token === null || false === $this->configuration->isDebugHar()) {
            return;
        }

        $filename = $this->getFilename($token);
        try {
            $this->harWriter->write($filename, $event->getRequest(), $event->getResponse());
            $this->logger->debug('HAR file written', ['filename' => $filename]);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to write HAR file', ['exception' => $e]);
        }
        $this->harWriter->cleanup($this->getDirectory($token));
    }

    protected function getFilename(string $token): string
    {
        return $this->getDirectory($token).'/'.date('Y-m-d').'.har';
    }

    protected function getDirectory(string $token): string
    {
        return $this->configuration->getHarDirectory().'/'.$token;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => ['onKernelTerminate', 10],
        ];
    }
}
