using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Events;
using Microsoft.AspNetCore.Mvc;
using MongoDB.Driver;
using System.Text.Json;

namespace CreditCardEnrollment.Common.Projection;

public abstract class ProjectionController : ControllerBase
{
    private readonly IMongoTransactionalProjectionOperator _mongoOperator;
    private readonly IDeserializer _deserializer;
    private readonly IMongoDatabase _database;
    private readonly ILogger<ProjectionController> _logger;

    protected ProjectionController(
        IMongoTransactionalProjectionOperator mongoOperator,
        IDeserializer deserializer,
        IMongoDatabase database,
        ILogger<ProjectionController> logger)
    {
        _mongoOperator = mongoOperator;
        _deserializer = deserializer;
        _database = database;
        _logger = logger;
    }

    protected async Task<IActionResult> ProcessProjectionHttpRequest(AmbarHttpRequest request, ProjectionHandler handler, string projectionName)
    {
        _logger.LogInformation(
            "Processing projection request. RequestId: {RequestId}, Projection: {ProjectionName}, EventId: {EventId}, EventName: {EventName}",
            HttpContext.TraceIdentifier,
            projectionName,
            request.SerializedEvent.EventId,
            request.SerializedEvent.EventName
        );

        try
        {
            var @event = _deserializer.Deserialize(request.SerializedEvent.JsonPayload);

            await _mongoOperator.ExecuteInTransaction(async () =>
            {
                _logger.LogInformation(
                    "Started MongoDB transaction for projection {ProjectionName}, EventId: {EventId}",
                    projectionName,
                    request.SerializedEvent.EventId
                );

                var collection = _database.GetCollection<ProjectedEvent>("ProjectionIdempotency_ProjectedEvent");
                var filter = Builders<ProjectedEvent>.Filter.And(
                    Builders<ProjectedEvent>.Filter.Eq(p => p.EventId, @event.EventId),
                    Builders<ProjectedEvent>.Filter.Eq(p => p.ProjectionName, projectionName)
                );

                var isAlreadyProjected = await collection.Find(filter).AnyAsync();
                if (isAlreadyProjected)
                {
                    _logger.LogInformation(
                        "Event {EventId} already projected for {ProjectionName}",
                        @event.EventId,
                        projectionName
                    );
                    return;
                }

                await collection.InsertOneAsync(new ProjectedEvent(@event.EventId, projectionName));
                
                try
                {
                    handler.HandleEvent(@event);
                    _logger.LogInformation(
                        "Successfully processed projection {ProjectionName} for event {EventId}",
                        projectionName,
                        @event.EventId
                    );
                }
                catch (Exception ex)
                {
                    _logger.LogError(
                        ex,
                        "Error during projection processing for {ProjectionName}, EventId: {EventId}",
                        projectionName,
                        @event.EventId
                    );
                    throw;
                }
            });

            _logger.LogInformation(
                "Successfully completed projection {ProjectionName} for event {EventId}",
                projectionName,
                @event.EventId
            );
            return Ok();
        }
        catch (Exception ex)
        {
            if (ex is ArgumentException argEx && argEx.Message.StartsWith("Unknown event type"))
            {
                _logger.LogWarning(
                    ex,
                    "Unknown event type in request. Projection: {ProjectionName}, EventId: {EventId}, EventName: {EventName}",
                    projectionName,
                    request.SerializedEvent.EventId,
                    request.SerializedEvent.EventName
                );
                return Ok();
            }

            _logger.LogError(
                ex,
                "Failed to process projection request. Projection: {ProjectionName}, EventId: {EventId}, EventName: {EventName}, JsonPayload: {JsonPayload}",
                projectionName,
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName,
                request.SerializedEvent.JsonPayload
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
