<?php

namespace App\Controller\Kobo;

use App\Kobo\Proxy\KoboProxyConfiguration;
use App\Kobo\Proxy\KoboStoreProxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/kobo/{accessKey}', name: 'kobo')]
class KoboUserProfileController extends AbstractController
{
    public function __construct(
        protected KoboProxyConfiguration $koboProxyConfiguration,
        protected KoboStoreProxy $koboStoreProxy,
    ) {
    }

    #[Route('/v1/user/profile')]
    public function userProfile(Request $request): Response
    {
        if ($this->koboProxyConfiguration->useProxy()) {
            return $this->koboStoreProxy->proxy($request);
        }

        $tokenParts = [];
        $tokenParts[] = base64_encode((string) json_encode([
            'typ' => 1,
            'ver' => 'v1',
            'ptyp' => 'ApiUserToken',
        ]));
        $tokenParts[] = base64_encode((string) json_encode([
            'LoyaltyMembershipVersion' => 2147483647,
            'LastModifiedTime' => -62135596800,
            'BuildVersion' => '1.0.0',
            'LifetimeTagsHash' => -790277027,
        ]));

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('X-Kobo-Apitoken', base64_encode((string) json_encode(['x-kobo-profile-token' => implode('.', $tokenParts)])));

        return $response;
    }
}
