<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd;

use Psr\Http\Message\ResponseInterface;

class JsonResponse
{
    /**
     * @var ResponseInterface
     */
    private $responseInterface;

    /**
     * @var string|null
     */
    private $cachedBody;

    private function __construct()
    {
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $jsonResponse = new self();
        $jsonResponse->responseInterface = $response;

        return $jsonResponse;
    }

    /**
     * @return GaleasErrorResponse
     *
     * @throws \Exception
     */
    public function getDecodedJsonAsGaleasErrorResponse()
    {
        $decodedJson = $this->getDecodedJson();

        if (
            400 <= $this->getStatusCode() &&
            $this->getStatusCode() < 600 &&
            property_exists($decodedJson, 'errors') &&
            $this->isArrayOfStrings($decodedJson->errors) &&
            property_exists($decodedJson, 'errorIdentifier') &&
            is_string($decodedJson->errorIdentifier) &&
            property_exists($decodedJson, 'errorMessage') &&
            is_string($decodedJson->errorMessage)
        ) {
            return GaleasErrorResponse::fromParameters(
                $decodedJson->errors,
                $decodedJson->errorIdentifier,
                $decodedJson->errorMessage
            );
        }

        throw new \Exception(sprintf('Could not handle json "%s". The response had status code %s', json_encode($decodedJson), $this->responseInterface->getStatusCode()));
    }

    /**
     * @throws \Exception
     */
    public function getDecodedJsonFromSuccessfulGaleasResponse(): \stdClass
    {
        $decodedJson = $this->getDecodedJson();

        if (
            200 <= $this->getStatusCode() &&
            $this->getStatusCode() < 300
        ) {
            return $decodedJson;
        }

        throw new \Exception(sprintf('Could not handle json "%s". The response had status code %s', json_encode($decodedJson), $this->responseInterface->getStatusCode()));
    }

    /**
     * @throws \Exception
     */
    public function getDecodedJsonArrayFromSuccessfulGaleasResponse(): array
    {
        $decodedJson = $this->getDecodedJsonArray();

        if (
            200 <= $this->getStatusCode() &&
            $this->getStatusCode() < 300
        ) {
            return $decodedJson;
        }

        throw new \Exception(sprintf('Could not handle json "%s". The response had status code %s', json_encode($decodedJson), $this->responseInterface->getStatusCode()));
    }

    public function getStatusCode(): int
    {
        return $this->responseInterface->getStatusCode();
    }

    /**
     * @throws \Exception
     */
    private function getDecodedJson(): \stdClass
    {
        if (null === $this->cachedBody) {
            $contents = $this->responseInterface->getBody()->getContents();
            $this->cachedBody = $contents ? $contents : '';
        }

        if ('' === $this->cachedBody) {
            throw new \Exception('No body to decode');
        }

        $decoded = json_decode($this->cachedBody);

        $jsonLastError = json_last_error();
        $jsonLastErrorMessage = json_last_error_msg();

        if (JSON_ERROR_NONE !== $jsonLastError) {
            throw new \Exception(sprintf('Error with body "%s". Error message is "%s"', $this->cachedBody, $jsonLastErrorMessage));
        }

        return $decoded;
    }

    /**
     * @throws \Exception
     */
    private function getDecodedJsonArray(): array
    {
        if (null === $this->cachedBody) {
            $contents = $this->responseInterface->getBody()->getContents();
            $this->cachedBody = $contents ? $contents : '';
        }

        if ('' === $this->cachedBody) {
            throw new \Exception('No body to decode');
        }

        $decoded = json_decode($this->cachedBody, true);

        $jsonLastError = json_last_error();
        $jsonLastErrorMessage = json_last_error_msg();

        if (JSON_ERROR_NONE !== $jsonLastError) {
            throw new \Exception(sprintf('Error with body "%s". Error message is "%s"', $this->cachedBody, $jsonLastErrorMessage));
        }

        return $decoded;
    }

    private function isArrayOfStrings(array $array): bool
    {
        foreach ($array as $item) {
            if (!is_string($item)) {
                return false;
            }
        }

        return true;
    }
}
