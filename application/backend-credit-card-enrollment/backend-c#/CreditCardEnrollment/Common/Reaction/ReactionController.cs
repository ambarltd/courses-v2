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
                "Processing reaction request. Event: {SerializedEvent}",
                JsonDocument.Parse(request.SerializedEvent).RootElement.ToString()
            );

            // Execute the reaction within a MongoDB transaction
            await _mongoOperator.ExecuteInTransaction(async () =>
            {
                _logger.LogInformation("Started MongoDB transaction");
                
                try
                {
                    // Process the reaction
                    await handler.React(request.SerializedEvent);
                    _logger.LogInformation("Successfully processed reaction");
                }
                catch (Exception ex)
                {
                    _logger.LogError(ex, "Error during reaction processing");
                    throw; // Re-throw to handle in outer catch
                }
            });

            _logger.LogInformation("Successfully completed reaction processing");
            return Ok();
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Failed to process reaction request. Event: {SerializedEvent}",
                request.SerializedEvent
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
