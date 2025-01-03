using MongoDB.Driver;
using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.SerializedEvent;
using MongoDB.Bson;

namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionController {
    private readonly MongoTransactionalProjectionOperator _mongoOperator;
    private readonly Deserializer _deserializer;

    protected ProjectionController(MongoTransactionalProjectionOperator mongoOperator, Deserializer deserializer) {
        _mongoOperator = mongoOperator;
        _deserializer = deserializer;
    }

    protected string ProcessProjectionHttpRequest(
        AmbarHttpRequest ambarHttpRequest,
        ProjectionHandler projectionHandler,
        string projectionName) {
        try {
            var @event = _deserializer.Deserialize(ambarHttpRequest.SerializedEvent);

            _mongoOperator.StartTransaction();
            var filter = Builders<BsonDocument>.Filter.And(
                Builders<BsonDocument>.Filter.Eq("eventId", @event.EventId),
                Builders<BsonDocument>.Filter.Eq("projectionName", projectionName)
            );

            var isAlreadyProjected = _mongoOperator.Operate()
                .GetCollection<BsonDocument>("ProjectionIdempotency_ProjectedEvent")
                .CountDocuments(filter) != 0;

            if (isAlreadyProjected) {
                return AmbarResponseFactory.SuccessResponse();
            }

            var projectedEvent = new BsonDocument
            {
                { "eventId", @event.EventId },
                { "projectionName", projectionName }
            };

            _mongoOperator.Operate()
                .GetCollection<BsonDocument>("ProjectionIdempotency_ProjectedEvent")
                .InsertOne(projectedEvent);

            projectionHandler.Project(@event);

            _mongoOperator.CommitTransaction();
            _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();

            return AmbarResponseFactory.SuccessResponse();
        } catch (Exception ex) when (ex.Message?.StartsWith("Unknown event type") == true) {
            _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            return AmbarResponseFactory.SuccessResponse();
        } catch (Exception ex) {
            _mongoOperator.AbortDanglingTransactionsAndReturnSessionToPool();
            return AmbarResponseFactory.RetryResponse(ex);
        }
    }
}