using CreditCardEnrollment.Application.Commands.RequestEnrollment;
using CreditCardEnrollment.Application.Queries.GetUserEnrollments;
using MediatR;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Controllers;

[ApiController]
[Route("[controller]")]
public class EnrollmentController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly ILogger<EnrollmentController> _logger;

    public EnrollmentController(IMediator mediator, ILogger<EnrollmentController> logger)
    {
        _mediator = mediator;
        _logger = logger;
    }

    [HttpPost("request")]
    public async Task<IActionResult> RequestEnrollment([FromBody] RequestEnrollmentCommand command)
    {
        _logger.LogInformation("Received enrollment request for product {ProductId}", command.ProductId);
        try
        {
            await _mediator.Send(command);
            _logger.LogInformation("Successfully processed enrollment request for product {ProductId}", command.ProductId);
            return Ok();
        }
        catch (Exception ex)
        {
            _logger.LogError("Error processing enrollment request for product {ProductId}: {Error}", command.ProductId, ex.Message);
            throw;
        }
    }

    [HttpGet]
    public async Task<ActionResult<List<EnrollmentListItemDto>>> GetUserEnrollments()
    {
        var sessionToken = Request.Headers["Authorization"].ToString();
        var maskedToken = sessionToken.Length > 6 ? sessionToken[..6] + "..." : sessionToken;
        _logger.LogInformation("Getting enrollments for session {SessionToken}", maskedToken);
        try
        {
            var query = new GetUserEnrollmentsQuery(sessionToken);
            var result = await _mediator.Send(query);
            // _logger.LogInformation("Retrieved {Count} enrollments for session {SessionToken}", result, maskedToken);
            return Ok(result);
        }
        catch (Exception ex)
        {
            _logger.LogError("Error retrieving enrollments for session {SessionToken}: {Error}", maskedToken, ex.Message);
            throw;
        }
    }
}
