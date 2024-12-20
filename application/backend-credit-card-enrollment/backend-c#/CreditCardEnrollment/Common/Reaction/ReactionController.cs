using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using Microsoft.AspNetCore.Mvc;
using System.Text.Json;

namespace CreditCardEnrollment.Common.Reaction;

public abstract class ReactionController : ControllerBase
{
    private readonly PostgresEventStore _eventStore;
    private readonly IMongoTransactionalProjectionOperator _mongoOperator;
    private readonly ILogger<ReactionController> _logger;

    protected ReactionController(
        PostgresEventStore eventStore,
        IMongoTransactionalProjectionOperator mongoOperator,
        ILogger<ReactionController> logger)
    {
        _eventStore = eventStore;
        _mongoOperator = mongoOperator;
        _logger = logger;
    }

    protected async Task<IActionResult> ProcessReactionHttpRequest(AmbarHttpRequest request, ReactionHandler handler)
    {
        try
        {
            _logger.LogInformation(
                "Processing reaction request. EventId: {EventId}, EventName: {EventName}",
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName
            );

            // Execute the reaction within a MongoDB transaction
            await _mongoOperator.ExecuteInTransaction(async () =>
            {
                _logger.LogInformation(
                    "Started MongoDB transaction for event {EventId}",
                    request.SerializedEvent.EventId
                );
                
                try
                {
                    // Process the reaction
                    await handler.React(request.SerializedEvent.JsonPayload);
                    _logger.LogInformation(
                        "Successfully processed reaction for event {EventId}",
                        request.SerializedEvent.EventId
                    );
                }
                catch (Exception ex)
                {
                    _logger.LogError(
                        ex,
                        "Error during reaction processing for event {EventId}",
                        request.SerializedEvent.EventId
                    );
                    throw; // Re-throw to handle in outer catch
                }
            });

            _logger.LogInformation(
                "Successfully completed reaction processing for event {EventId}",
                request.SerializedEvent.EventId
            );
            return Ok();
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Failed to process reaction request. EventId: {EventId}, EventName: {EventName}, JsonPayload: {JsonPayload}",
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName,
                request.SerializedEvent.JsonPayload
            );
            return StatusCode(500, new
            {
                error = "Failed to process reaction",
                message = ex.Message,
                details = ex.ToString()
            });
        }
    }
}
