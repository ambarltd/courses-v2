using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Reaction;
using CreditCardEnrollment.Common.SerializedEvent;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.CreditCard.Enrollment.Reaction;

[ApiController]
[Route("api/v1/credit_card/enrollment/reaction")]
[Produces("application/json")]
[Consumes("application/json")]
public class EnrollmentReactionController : ReactionController {
    private readonly ReviewEnrollmentReactionHandler _reviewEnrollmentReactionHandler;

    public EnrollmentReactionController(
        PostgresTransactionalEventStore eventStore,
        MongoTransactionalProjectionOperator mongoOperator,
        Deserializer deserializer,
        ReviewEnrollmentReactionHandler reviewEnrollmentReactionHandler)
        : base(eventStore, mongoOperator, deserializer) {
        _reviewEnrollmentReactionHandler = reviewEnrollmentReactionHandler;
    }

    [HttpPost("review_enrollment")]
    public IActionResult ReactWithReviewEnrollment([FromBody] AmbarHttpRequest request) {
        return new ContentResult {
            Content = ProcessReactionHttpRequest(
                request, 
                _reviewEnrollmentReactionHandler
            ),
            ContentType = "application/json",
            StatusCode = 200
        };
    }
}