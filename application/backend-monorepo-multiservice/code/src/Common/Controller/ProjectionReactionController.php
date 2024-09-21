<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Controller;

use Galeas\Api\Common\Event\EventDeserializer;
use Galeas\Api\Common\Event\Exception\FoundBadJsonInProjectionOrReaction;
use Galeas\Api\Common\Event\SerializedEvent;
use Galeas\Api\Common\ExceptionBase\BaseException;
use Galeas\Api\Service\Logger\PhpOutLogger;
use Galeas\Api\Service\QueueProcessor\EventProjector;
use Galeas\Api\Service\QueueProcessor\EventReactor;
use Galeas\Api\Service\RequestMapper\Exception\InvalidContentType;
use Galeas\Api\Service\RequestMapper\Exception\InvalidJson;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectionReactionController extends AbstractController
{
    private PhpOutLogger $phpOutLogger;

    public function setPhpOutLogger(PhpOutLogger $phpOutLogger): void
    {
        $this->phpOutLogger = $phpOutLogger;
    }

    public function jsonPostRequestJsonResponse(
        Request $request,
        EventProjector|EventReactor $projectorOrReactor,
        int $successStatusCode
    ): JsonResponse {
        try {
            $json = $this->requestJsonToRequestArray($request);
            $projectionPayload = [
                'data_source_id' => $json["data_source_id"],
                'data_source_description' => $json["data_source_description"],
                'data_destination_id' => $json["data_destination_id"],
                'data_destination_description' => $json["data_destination_description"],
                'payload' => $json["payload"],
            ];

            $serializedEvent = SerializedEvent::fromAmbarJson(
                json_encode($projectionPayload["payload"])
            );
            $event = EventDeserializer::serializedEventsToEvents([$serializedEvent])[0];
            $success = false;
            if ($projectorOrReactor instanceof EventProjector) {
                $projectorOrReactor->project($event);
                $success = true;
            }

            if ($projectorOrReactor instanceof EventReactor) {
                $projectorOrReactor->react($event);
                $success = true;
            }

            if (!$success) {
                throw new \Exception("This endpoint needs a projector or reactor");
            }

            return JsonResponse::fromJsonString(json_encode([
                'result' => [
                    'success' => new \stdClass(),
                ],
            ]), $successStatusCode);
        } catch (BaseException $exception) {
            $errorMessage = substr($exception->getMessage(), 0, 8192);
            $stackTrace = substr($exception->getTraceAsString(), 0, 8192);
            $this->phpOutLogger->warning(sprintf(
                "BaseException caught, classFQN %s, message: %s, stack trace: %s",
                $exception::class,
                $errorMessage,
                $stackTrace
            ));

            return JsonResponse::fromJsonString(
                json_encode([
                    'result' => [
                        'error' => [
                            'policy' => $exception::getHttpCode() > 300 ? 'must_retry': 'keep_going',
                            'class' => $exception::getErrorIdentifier(),
                            'description' => $errorMessage,
                        ]
                    ],
                ]),
                $exception::getHttpCode()
            );
        } catch (\Throwable $throwable) {
            $errorMessage = substr($throwable->getMessage(), 0, 8192);
            $stackTrace = substr($throwable->getTraceAsString(), 0, 8192);
            $this->phpOutLogger->warning(sprintf(
                "Throwable caught, classFQN %s, message: %s, stack trace: %s",
                $throwable::class,
                $errorMessage,
                $stackTrace
            ));

            return JsonResponse::fromJsonString(
                json_encode([
                    'result' => [
                        'error' => [
                            'policy' => 'must_retry',
                            'class' => 'internal_server_error',
                            'description' => $errorMessage,
                        ]
                    ],
                ]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @throws InvalidJson|InvalidContentType
     */
    private function requestJsonToRequestArray(Request $request): array
    {
        // No need for content type enforcement - to fix on ambar side
//        $contentType = $request->headers->get('content-type');
//
//        if (!is_string($contentType)) {
//            throw new InvalidContentType();
//        }
//        if (
//            is_string($contentType) &&
//            'application/json' !== substr($contentType, 0, 16)
//        ) {
//            throw new InvalidContentType();
//        }

        try {
            $content = $request->getContent();
        } catch (\LogicException $exception) {
            throw new InvalidContentType();
        }

        $requestArray = [];
        if (!empty($content)) {
            $requestArray = json_decode($content, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidJson(sprintf('%s is not in a valid JSON format', $content));
            }
        }

        if (
            array_key_exists("data_source_id", $requestArray) &&
            array_key_exists("data_source_description", $requestArray) &&
            array_key_exists("data_destination_id", $requestArray) &&
            array_key_exists("data_destination_id", $requestArray) &&
            array_key_exists("payload", $requestArray) &&
            $requestArray["data_source_id"] !== null &&
            $requestArray["data_source_description"] !== null &&
            $requestArray["data_destination_id"] !== null &&
            $requestArray["data_destination_description"] !== null &&
            $requestArray["payload"] !== null
        ) {
            return $requestArray;
        }

        throw new FoundBadJsonInProjectionOrReaction();
    }
}
