using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.SerializedEvent;
using MongoDB.Bson;

namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionController {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;
    private readonly Deserializer _deserializer;
    private readonly ILogger<ProjectionController> _logger;

    protected ProjectionController(
        MongoTransactionalProjectionOperator mongoOperator, 
        Deserializer deserializer,
        ILogger<ProjectionController> logger
    ) {
        _mongoOperator = mongoOperator;
        _deserializer = deserializer;
        _logger = logger;
    }

    protected string ProcessProjectionHttpRequest(
        AmbarHttpRequest ambarHttpRequest,
        ProjectionHandler projectionHandler,
        string projectionName) {
        try {
            _logger.LogDebug(
                "Starting to process projection for event name: {EventName} using handler: {HandlerName}", 
                ambarHttpRequest.SerializedEvent.EventName,
                projectionHandler.GetType().Name
            );
            var @event = _deserializer.Deserialize(ambarHttpRequest.SerializedEvent);

            _mongoOperator.StartTransaction();
            var isAlreadyProjected = _mongoOperator
                .CountDocuments<BsonDocument>(
                    "ProjectionIdempotency_ProjectedEvent", 
                    doc => doc["eventId"] == @event.EventId && 
                           doc["projectionName"] == projectionName
                ) != 0;

            if (isAlreadyProjected) {
                _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();
                _logger.LogDebug(
                    "Duplication projection ignored for event name: {EventName} using handler: {HandlerName}", 
                    ambarHttpRequest.SerializedEvent.EventName,
                    projectionHandler.GetType().Name
                );
                return AmbarResponseFactory.SuccessResponse();
            }

            var projectedEvent = new BsonDocument
            {
                { "eventId", @event.EventId },
                { "projectionName", projectionName }
            };

            _mongoOperator
                .InsertOne("ProjectionIdempotency_ProjectedEvent", projectedEvent);

            projectionHandler.Project(@event);

            _mongoOperator.CommitTransaction();
            _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();

            _logger.LogDebug(
                "Projection successfully processed for event name: {EventName} using handler: {HandlerName}", 
                ambarHttpRequest.SerializedEvent.EventName,
                projectionHandler.GetType().Name
            );
            return AmbarResponseFactory.SuccessResponse();
        } catch (Exception ex) when (ex.Message?.StartsWith("Unknown event type") == true) {
            _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            
            _logger.LogDebug(
                "Unknown event in projection ignored for event name: {EventName} using handler: {HandlerName}", 
                ambarHttpRequest.SerializedEvent.EventName,
                projectionHandler.GetType().Name
            );
            return AmbarResponseFactory.SuccessResponse();
        } catch (Exception ex) {
            _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            _logger.LogError(
                "Exception in ProcessProjectionHttpRequest: {0}, {1}. For event name: {EventName} using handler: {HandlerName}", 
                ex.Message, 
                ex.StackTrace,
                ambarHttpRequest.SerializedEvent.EventName,
                projectionHandler.GetType().Name
            );
            return AmbarResponseFactory.RetryResponse(ex);
        }
    }
}