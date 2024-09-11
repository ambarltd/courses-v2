<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

abstract class BaseHttpContext extends BaseContext
{
    /**
     * @var string
     */
    protected $baseUrl;

    const DEFAULT_METADATA = [
        'environment' => 'unknown',
        'deviceOrientation' => 'unknown',
        'devicePlatform' => 'unknown',
        'deviceModel' => 'unknown',
        'deviceOSVersion' => 'unknown',
    ];

    public function __construct()
    {
        $this->baseUrl = 'https://'.getenv('API_DOMAIN');
        parent::__construct();
    }

    /**
     * @param string $sessionToken
     */
    protected function makeJsonPostRequestAndGetResponse(
        string $url,
        array $payload,
        ?string $sessionToken = null
    ): JsonResponse {
        if (!array_key_exists('metadata', $payload)) {
            $payload = array_merge($payload, ['metadata' => self::DEFAULT_METADATA]);
        }

        // could be changed to header session token
        if (is_string($sessionToken)) {
            $payload['metadata']['withSessionToken'] = $sessionToken;
        }

        $client = new Client(
            [
                'verify' => false,
            ]
        );
        try {
            $response = $client->post(
                $this->baseUrl.'/'.$url,
                [
                    RequestOptions::JSON => $payload,
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
        } catch (ServerException $e) {
            $response = $e->getResponse();
        }

        if (null === $response) {
            throw new \RuntimeException('Could not get response');
        }

        return JsonResponse::fromResponse($response);
    }

    protected function getDecodedJsonFromResponse(ResponseInterface $response): \stdClass
    {
        return json_decode(
            $response
                ->getBody()
                ->getContents()
        );
    }
}
