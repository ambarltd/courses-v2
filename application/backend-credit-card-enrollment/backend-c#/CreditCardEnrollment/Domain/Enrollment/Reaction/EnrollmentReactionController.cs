using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Reaction;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Domain.Enrollment.Reaction;

[ApiController]
[Route("api/v1/credit_card/enrollment/reaction")]
public class EnrollmentReactionController(
    PostgresEventStore eventStore,
    IMongoTransactionalProjectionOperator mongoOperator,
    ILogger<EnrollmentReactionController> logger,
    ReviewEnrollmentReactionHandler reviewEnrollmentHandler)
    : ReactionController(eventStore, mongoOperator, logger)
{
    [HttpPost("review_enrollment")]
    public async Task<IActionResult> ReviewEnrollment([FromBody] AmbarHttpRequest request)
    {
        logger.LogInformation(
            "Received review_enrollment request. RequestId: {RequestId}, ContentLength: {ContentLength}",
            HttpContext.TraceIdentifier,
            request.SerializedEvent.Length
        );

        try
        {
            var result = await ProcessReactionHttpRequest(request, reviewEnrollmentHandler);
            
            logger.LogInformation(
                "Completed review_enrollment request. RequestId: {RequestId}, StatusCode: {StatusCode}",
                HttpContext.TraceIdentifier,
                (result as ObjectResult)?.StatusCode ?? (result as StatusCodeResult)?.StatusCode
            );
            
            return result;
        }
        catch (Exception ex)
        {
            logger.LogError(
                ex,
                "Error processing review_enrollment request. RequestId: {RequestId}",
                HttpContext.TraceIdentifier
            );
            throw; // Let the framework handle the exception
        }
    }
}
