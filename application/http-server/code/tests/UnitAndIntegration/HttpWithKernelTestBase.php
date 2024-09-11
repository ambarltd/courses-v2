<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * {@inheritdoc}
 */
abstract class HttpWithKernelTestBase extends KernelTestBase
{
    public function getApiUrl(string $urlWithoutBaseDomain): ResponseInterface
    {
        return $this->getUrl(
            $this->getContainer()->getParameter('api_domain').$urlWithoutBaseDomain
        );
    }

    public function getUrl(string $url): ResponseInterface
    {
        $client = new Client();

        return $client->request(
            'GET',
            $url,
            [
                'http_errors' => false,
                'verify' => false,
            ]
        );
    }
}
