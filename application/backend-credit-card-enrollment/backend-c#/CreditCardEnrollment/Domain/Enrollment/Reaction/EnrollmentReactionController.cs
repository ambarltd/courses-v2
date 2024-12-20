using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Reaction;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Domain.Enrollment.Reaction;

[ApiController]
[Route("api/v1/credit_card/enrollment/reaction")]
public class EnrollmentReactionController : ReactionController
{
    private readonly ReviewEnrollmentReactionHandler _reviewEnrollmentHandler;
    private readonly ILogger<EnrollmentReactionController> _logger;

    public EnrollmentReactionController(
        PostgresEventStore eventStore,
        IMongoTransactionalProjectionOperator mongoOperator,
        ILogger<EnrollmentReactionController> logger,
        ReviewEnrollmentReactionHandler reviewEnrollmentHandler)
        : base(eventStore, mongoOperator, logger)
    {
        _reviewEnrollmentHandler = reviewEnrollmentHandler;
        _logger = logger;
    }

    [HttpPost("review_enrollment")]
    [Consumes("application/json")]
    [Produces("application/json")]
    public async Task<IActionResult> ReviewEnrollment([FromBody] AmbarHttpRequest request)
    {
        _logger.LogInformation(
            "Received review_enrollment request. RequestId: {RequestId}, EventId: {EventId}, EventName: {EventName}",
            HttpContext.TraceIdentifier,
            request?.SerializedEvent?.EventId,
            request?.SerializedEvent?.EventName
        );

        if (request?.SerializedEvent == null)
        {
            _logger.LogWarning("Invalid request: SerializedEvent is null. RequestId: {RequestId}", HttpContext.TraceIdentifier);
            return BadRequest(new { error = "SerializedEvent is required" });
        }

        try
        {
            _logger.LogDebug(
                "Processing event. EventId: {EventId}, EventName: {EventName}, AggregateId: {AggregateId}, JsonPayload: {JsonPayload}",
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName,
                request.SerializedEvent.AggregateId,
                request.SerializedEvent.JsonPayload
            );

            var result = await ProcessReactionHttpRequest(request, _reviewEnrollmentHandler);
            
            _logger.LogInformation(
                "Completed review_enrollment request. RequestId: {RequestId}, StatusCode: {StatusCode}",
                HttpContext.TraceIdentifier,
                (result as ObjectResult)?.StatusCode ?? (result as StatusCodeResult)?.StatusCode
            );
            
            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error processing review_enrollment request. RequestId: {RequestId}, EventId: {EventId}, EventName: {EventName}",
                HttpContext.TraceIdentifier,
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName
            );
            throw; // Let the framework handle the exception
        }
    }
}
