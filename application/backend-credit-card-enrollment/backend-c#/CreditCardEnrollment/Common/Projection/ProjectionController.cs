using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Events;
using Microsoft.AspNetCore.Mvc;
using MongoDB.Driver;

namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionController(
    IMongoTransactionalProjectionOperator mongoOperator,
    IDeserializer deserializer,
    IMongoDatabase database,
    ILogger<ProjectionController> logger)
    : ControllerBase
{
    protected async Task<IActionResult> ProcessProjectionHttpRequest(AmbarHttpRequest request, ProjectionHandler handler, string projectionName)
    {
        logger.LogInformation(
            "Processing projection request. RequestId: {RequestId}, Projection: {ProjectionName}",
            HttpContext.TraceIdentifier,
            projectionName
        );

        try
        {
            var @event = deserializer.Deserialize(request.SerializedEvent);

            await mongoOperator.ExecuteInTransaction(async () =>
            {
                logger.LogInformation("Started MongoDB transaction for projection {ProjectionName}", projectionName);

                var collection = database.GetCollection<ProjectedEvent>("ProjectionIdempotency_ProjectedEvent");
                var filter = Builders<ProjectedEvent>.Filter.And(
                    Builders<ProjectedEvent>.Filter.Eq(p => p.EventId, @event.EventId),
                    Builders<ProjectedEvent>.Filter.Eq(p => p.ProjectionName, projectionName)
                );

                var isAlreadyProjected = await collection.Find(filter).AnyAsync();
                if (isAlreadyProjected)
                {
                    logger.LogInformation("Event {EventId} already projected for {ProjectionName}", @event.EventId, projectionName);
                    return;
                }

                await collection.InsertOneAsync(new ProjectedEvent(@event.EventId, projectionName));
                
                try
                {
                    handler.HandleEvent(@event);
                    logger.LogInformation("Successfully processed projection {ProjectionName}", projectionName);
                }
                catch (Exception ex)
                {
                    logger.LogError(ex, "Error during projection processing for {ProjectionName}", projectionName);
                    throw;
                }
            });

            logger.LogInformation("Successfully completed projection {ProjectionName}", projectionName);
            return Ok();
        }
        catch (Exception ex)
        {
            if (ex is ArgumentException argEx && argEx.Message.StartsWith("Unknown event type"))
            {
                logger.LogWarning(ex, "Unknown event type. Skipping projection.");
                return Ok();
            }

            logger.LogError(
                ex,
                "Failed to process projection request. Projection: {ProjectionName}, Event: {SerializedEvent}",
                projectionName,
                request.SerializedEvent
            );
            return StatusCode(500, new
            {
                error = "Failed to process projection",
                message = ex.Message,
                details = ex.ToString()
            });
        }
    }
}
