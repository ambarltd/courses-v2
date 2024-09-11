<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd;

class GaleasErrorResponse
{
    /**
     * @var string[]
     */
    private $errors;

    /**
     * @var string
     */
    private $errorIdentifier;

    /**
     * @var string
     */
    private $errorMessage;

    private function __construct()
    {
    }

    /**
     * @param string[] $errors
     */
    public static function fromParameters(
        array $errors,
        string $errorIdentifier,
        string $errorMessage
    ): self {
        $errorResponse = new self();
        $errorResponse->errors = $errors;
        $errorResponse->errorIdentifier = $errorIdentifier;
        $errorResponse->errorMessage = $errorMessage;

        return $errorResponse;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorIdentifier(): string
    {
        return $this->errorIdentifier;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
