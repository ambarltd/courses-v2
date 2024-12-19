using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using Microsoft.AspNetCore.Mvc;
using System.Text.Json;

namespace CreditCardEnrollment.Common.Reaction;

public abstract class ReactionController(
    PostgresEventStore eventStore,
    IMongoTransactionalProjectionOperator mongoOperator,
    ILogger<ReactionController> logger)
    : ControllerBase
{
    private readonly PostgresEventStore _eventStore = eventStore;

    protected async Task<IActionResult> ProcessReactionHttpRequest(AmbarHttpRequest request, ReactionHandler handler)
    {
        try
        {
            logger.LogInformation(
                "Processing reaction request. Event: {SerializedEvent}",
                JsonDocument.Parse(request.SerializedEvent).RootElement.ToString()
            );

            // Execute the reaction within a MongoDB transaction
            await mongoOperator.ExecuteInTransaction(async () =>
            {
                logger.LogInformation("Started MongoDB transaction");
                
                try
                {
                    // Process the reaction
                    await handler.React(request.SerializedEvent);
                    logger.LogInformation("Successfully processed reaction");
                }
                catch (Exception ex)
                {
                    logger.LogError(ex, "Error during reaction processing");
                    throw; // Re-throw to handle in outer catch
                }
            });

            logger.LogInformation("Successfully completed reaction processing");
            return Ok();
        }
        catch (Exception ex)
        {
            logger.LogError(
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
