<?php

namespace App\Controller\Kobo;

use App\Kobo\Proxy\KoboProxyConfiguration;
use App\Kobo\Proxy\KoboStoreProxy;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/kobo/{accessKey}', name: 'kobo')]
class KoboAuthDeviceController extends AbstractController
{
    public function __construct(
        protected KoboProxyConfiguration $koboProxyConfiguration,
        protected KoboStoreProxy $koboStoreProxy,
        protected LoggerInterface $logger)
    {
    }

    /**
     * @throws GuzzleException
     */
    #[Route('/v1/auth/device', methods: ['GET', 'POST'])]
    #[Route('/v1/auth/refresh', methods: ['GET', 'POST'])]
    public function authDevice(Request $request): Response
    {
        if ($this->koboProxyConfiguration->useProxy()) {
            return $this->koboStoreProxy->proxy(
                $request, ['stream' => true]
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
