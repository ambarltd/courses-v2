using CreditCardEnrollment.Common.Command;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.Projection;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.CreditCard.Enrollment.Command;

[ApiController]
[Route("api/v1/credit_card/enrollment")]
[Produces("application/json")]
[Consumes("application/json")]
public class EnrollmentCommandController : CommandController {
    private readonly RequestEnrollmentCommandHandler _requestEnrollmentCommandHandler;

    public EnrollmentCommandController(
        PostgresTransactionalEventStore postgresTransactionalEventStore,
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
        ILogger<EnrollmentCommandController> logger,
        RequestEnrollmentCommandHandler requestEnrollmentCommandHandler) 
        : base(postgresTransactionalEventStore, mongoTransactionalProjectionOperator, logger) {
        _requestEnrollmentCommandHandler = requestEnrollmentCommandHandler;
    }

    [HttpPost("request-enrollment")]
    [ProducesResponseType(typeof(object), StatusCodes.Status200OK)]
    public IActionResult RequestEnrollment(
        [FromHeader(Name = "X-With-Session-Token")] string sessionToken,
        [FromBody] RequestEnrollmentHttpRequest request) {
        var command = new RequestEnrollmentCommand {
            SessionToken = sessionToken,
            ProductId = request.ProductId,
            AnnualIncomeInCents = request.AnnualIncomeInCents
        };

        ProcessCommand(command, _requestEnrollmentCommandHandler);
        return new OkObjectResult(new {});
    }
}