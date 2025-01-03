using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.CreditCard.Enrollment.Query;

[ApiController]
[Route("api/v1/credit_card/enrollment")]
[Produces("application/json")]
[Consumes("application/json")] 
public class EnrollmentQueryController : QueryController {
    private readonly GetUserEnrollmentsQueryHandler _getUserEnrollmentsQueryHandler;

    public EnrollmentQueryController(
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
        GetUserEnrollmentsQueryHandler getUserEnrollmentsQueryHandler) 
        : base(mongoTransactionalProjectionOperator) {
        _getUserEnrollmentsQueryHandler = getUserEnrollmentsQueryHandler;
    }

    [HttpPost("list-enrollments")]
    [ProducesResponseType(typeof(object), StatusCodes.Status200OK)]
    public IActionResult ListEnrollments(
        [FromHeader(Name = "X-With-Session-Token")] string sessionToken) {
        var query = new GetUserEnrollmentsQuery {
            SessionToken = sessionToken
        };

        return new OkObjectResult(ProcessQuery(query, _getUserEnrollmentsQueryHandler));
    }
}