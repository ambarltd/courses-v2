<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Galeas\Api\JsonSchema\AnnotationReaderFailed;
use Galeas\Api\JsonSchema\SchemaAnnotationReader;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SchemaAnnotationReaderTest extends UnitTestBase
{
    public function testRequestAnnotationExtraction(): void
    {
        $schemaAnnotationReader = new SchemaAnnotationReader();

        Assert::assertEquals('example', $schemaAnnotationReader->getRequestSchema(RequestStub::class.'::requestExample'));
        Assert::assertEquals('example2', $schemaAnnotationReader->getRequestSchema(RequestStub::class.'::requestExample2'));
        Assert::assertEquals('example3', $schemaAnnotationReader->getRequestSchema(RequestStub::class.'::requestExample3'));
        Assert::assertEquals('example4', $schemaAnnotationReader->getRequestSchema(RequestStub::class.'::requestExample4'));
        Assert::assertEquals('example5', $schemaAnnotationReader->getRequestSchema(RequestStub::class.'::requestExample5'));
        Assert::assertEquals('example6', $schemaAnnotationReader->getRequestSchema(RequestStub::class.'::requestExample6'));

        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getRequestSchema(ResponseStub::class.'::requestExample');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getRequestSchema(ResponseStub::class.'::requestExample2');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getRequestSchema(ResponseStub::class.'::requestExample3');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getRequestSchema(ResponseStub::class.'::requestExample4');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getRequestSchema(ResponseStub::class.'::requestExample5');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getRequestSchema(ResponseStub::class.'::requestExample6');
        });
    }

    private function assertAnnotationReaderFailedIsCalled(callable $callable): void
    {
        try {
            $callable();
            Assert::fail('Expected exception');
        } catch (AnnotationReaderFailed $exception) {
            Assert::assertTrue(true);
        }
    }

    public function testResponseAnnotationExtraction(): void
    {
        $schemaAnnotationReader = new SchemaAnnotationReader();

        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getResponseSchema(RequestStub::class.'::requestExample');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getResponseSchema(RequestStub::class.'::requestExample2');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getResponseSchema(RequestStub::class.'::requestExample3');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getResponseSchema(RequestStub::class.'::requestExample4');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getResponseSchema(RequestStub::class.'::requestExample5');
        });
        $this->assertAnnotationReaderFailedIsCalled(function () use ($schemaAnnotationReader): void {
            $schemaAnnotationReader->getResponseSchema(RequestStub::class.'::requestExample6');
        });

        Assert::assertEquals('example', $schemaAnnotationReader->getResponseSchema(ResponseStub::class.'::responseExample'));
        Assert::assertEquals('example2', $schemaAnnotationReader->getResponseSchema(ResponseStub::class.'::responseExample2'));
        Assert::assertEquals('example3', $schemaAnnotationReader->getResponseSchema(ResponseStub::class.'::responseExample3'));
        Assert::assertEquals('example4', $schemaAnnotationReader->getResponseSchema(ResponseStub::class.'::responseExample4'));
        Assert::assertEquals('example5', $schemaAnnotationReader->getResponseSchema(ResponseStub::class.'::responseExample5'));
        Assert::assertEquals('example6', $schemaAnnotationReader->getResponseSchema(ResponseStub::class.'::responseExample6'));
    }
}

class RequestStub
{
    /**
     * @RequestSchema(name="example")
     */
    public function requestExample(string $param1): void
    {
    }

    /**
     * @RequestSchema(name="example2", otherProperty="somethingElse")
     */
    public function requestExample2(string $param1): void
    {
    }

    /**
     * @RequestSchema ( name = "example3" otherProperty="other")
     */
    public function requestExample3(string $param1): void
    {
    }

    /**
     * @RequestSchema ( name = 'example4' otherProperty="other")
     */
    public function requestExample4(string $param1): void
    {
    }

    /**
     * @RequestSchema ( name = "example5" otherProperty= 'other')
     */
    public function requestExample5(string $param1): void
    {
    }

    /**
     * @RequestSchema (name='example6' otherProperty='other')
     */
    public function requestExample6(string $param1): void
    {
    }
}

class ResponseStub
{
    /**
     * @ResponseSchema(name="example")
     */
    public function responseExample(string $param1): void
    {
    }

    /**
     * @ResponseSchema(name="example2", otherProperty="somethingElse")
     */
    public function responseExample2(string $param1): void
    {
    }

    /**
     * @ResponseSchema ( name = "example3" otherProperty="other")
     */
    public function responseExample3(string $param1): void
    {
    }

    /**
     * @ResponseSchema ( name = 'example4' otherProperty="other")
     */
    public function responseExample4(string $param1): void
    {
    }

    /**
     * @ResponseSchema ( name = "example5" otherProperty= 'other')
     */
    public function responseExample5(string $param1): void
    {
    }

    /**
     * @ResponseSchema (name='example6' otherProperty='other')
     */
    public function responseExample6(string $param1): void
    {
    }
}
