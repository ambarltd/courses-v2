import { MongoTransactionalProjectionOperator } from './MongoTransactionalProjectionOperator';
import { Deserializer } from '../serializedEvent/Deserializer';
import { AmbarHttpRequest } from '../ambar/AmbarHttpRequest';
import { AmbarResponseFactory } from '../ambar/AmbarResponseFactory';
import { ProjectionHandler } from './ProjectionHandler';
import { log } from '../util/Logger';

export abstract class ProjectionController {
    protected constructor(
        private readonly mongoOperator: MongoTransactionalProjectionOperator,
        private readonly deserializer: Deserializer,
    ) {}

    protected async processProjectionHttpRequest(
        ambarHttpRequest: AmbarHttpRequest,
        projectionHandler: ProjectionHandler,
        projectionName: string
    ): Promise<string> {
        try {
            log.debug(
                `Starting to process projection for event name: ${ambarHttpRequest.payload.event_name} using handler: ${projectionHandler.constructor.name}`
            );

            const event = this.deserializer.deserialize(ambarHttpRequest.payload);

            await this.mongoOperator.startTransaction();

            const isAlreadyProjected = await this.mongoOperator.countDocuments(
                'ProjectionIdempotency_ProjectedEvent',
                {
                    eventId: event.eventId,
                    projectionName: projectionName
                }
            ) !== 0;

            if (isAlreadyProjected) {
                await this.mongoOperator.abortDanglingTransactionsAndReturnSessionToPool();
                log.debug(
                    `Duplication projection ignored for event name: ${ambarHttpRequest.payload.event_name} using handler: ${projectionHandler.constructor.name}`
                );
                return AmbarResponseFactory.successResponse();
            }

            // Record projected event
            await this.mongoOperator.insertOne('ProjectionIdempotency_ProjectedEvent', {
                eventId: event.eventId,
                projectionName: projectionName
            });

            await projectionHandler.project(event);

            await this.mongoOperator.commitTransaction();
            await this.mongoOperator.abortDanglingTransactionsAndReturnSessionToPool();

            log.debug(
                `Projection successfully processed for event name: ${ambarHttpRequest.payload.event_name} using handler: ${projectionHandler.constructor.name}`
            );
            return AmbarResponseFactory.successResponse();

        } catch (ex) {
            if (ex instanceof Error && ex.message.startsWith('Unknown event type')) {
                await this.mongoOperator.abortDanglingTransactionsAndReturnSessionToPool();

                log.debug(
                    `Unknown event in projection ignored for event name: ${ambarHttpRequest.payload.event_name} using handler: ${projectionHandler.constructor.name}`
                );
                return AmbarResponseFactory.successResponse();
            }

            await this.mongoOperator.abortDanglingTransactionsAndReturnSessionToPool();
            log.error(
                `Exception in ProcessProjectionHttpRequest: ${ex}. For event name: ${ambarHttpRequest.payload.event_name} using handler: ${projectionHandler.constructor.name}`
            );
            return AmbarResponseFactory.retryResponse(ex as Error);
        }
    }
}