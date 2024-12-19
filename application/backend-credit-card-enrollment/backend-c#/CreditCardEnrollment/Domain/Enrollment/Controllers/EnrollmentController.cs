using CreditCardEnrollment.Domain.Enrollment.Controllers.RequestEnrollment;
using CreditCardEnrollment.Domain.Enrollment.Queries;
using MediatR;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Domain.Enrollment.Controllers;

[ApiController]
[Route("api/v1/credit_card/enrollment")]
public class EnrollmentController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly ILogger<EnrollmentController> _logger;

    public EnrollmentController(IMediator mediator, ILogger<EnrollmentController> logger)
    {
        _mediator = mediator;
        _logger = logger;
    }

    [HttpPost("request-enrollment")]
    public async Task<IActionResult> RequestEnrollment([FromBody] RequestEnrollmentHttpRequest request)
    {
        var sessionToken = Request.Headers["X-With-Session-Token"].ToString();
        if (string.IsNullOrEmpty(sessionToken))
        {
            _logger.LogWarning("Missing X-With-Session-Token header");
            return BadRequest(new { error = "Missing session token" });
        }

        var maskedToken = sessionToken.Length > 6 ? sessionToken[..6] + "..." : sessionToken;

        _logger.LogInformation(
            "Received enrollment request - Product: {ProductId}, Annual Income: {AnnualIncome}, Session: {SessionToken}, Path: {Path}", 
            request.ProductId, 
            request.AnnualIncomeInCents,
            maskedToken,
            Request.Path);

        try
        {
            var command = new RequestEnrollmentCommand(
                sessionToken,
                request.ProductId,
                request.AnnualIncomeInCents);

            var result = await _mediator.Send(command);
            
            _logger.LogInformation(
                "Successfully processed enrollment request - Product: {ProductId}, Session: {SessionToken}", 
                request.ProductId,
                maskedToken);
            
            return Ok(result);
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error processing enrollment request - Product: {ProductId}, Session: {SessionToken}, Error: {Error}", 
                request.ProductId,
                maskedToken,
                ex.Message);
            
            return StatusCode(500, new { error = "An error occurred while processing the enrollment request" });
        }
    }

    [HttpPost("list-enrollments")]
    public async Task<ActionResult<List<EnrollmentListItemDto>>> GetUserEnrollments()
    {
        var sessionToken = Request.Headers["X-With-Session-Token"].ToString();
        if (string.IsNullOrEmpty(sessionToken))
        {
            _logger.LogWarning("Missing X-With-Session-Token header");
            return BadRequest(new { error = "Missing session token" });
        }

        var maskedToken = sessionToken.Length > 6 ? sessionToken[..6] + "..." : sessionToken;
        
        _logger.LogInformation(
            "Getting enrollments - Session: {SessionToken}, Path: {Path}", 
            maskedToken,
            Request.Path);
        
        try
        {
            var query = new GetUserEnrollmentsQuery(sessionToken);
            var result = await _mediator.Send(query);
            
            return Ok(result);
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error retrieving enrollments - Session: {SessionToken}, Error: {Error}",
                maskedToken,
                ex.Message);
            
            return StatusCode(500, new { error = "An error occurred while retrieving enrollments" });
        }
    }

    [HttpGet("{*url}")]
    [HttpPost("{*url}")]
    [HttpPut("{*url}")]
    [HttpDelete("{*url}")]
    [HttpPatch("{*url}")]
    [ApiExplorerSettings(IgnoreApi = true)]
    [Route("{*url}", Order = 999)]
    public IActionResult HandleUnsupportedEndpoint()
    {
        var sessionToken = Request.Headers["X-With-Session-Token"].ToString();
        var maskedToken = !string.IsNullOrEmpty(sessionToken) && sessionToken.Length > 6 
            ? sessionToken[..6] + "..." 
            : sessionToken;

        _logger.LogWarning(
            "Unsupported endpoint accessed - Path: {Path}, Method: {Method}, Session: {SessionToken}", 
            Request.Path, 
            Request.Method,
            maskedToken);
            
        return NotFound(new { error = "Endpoint not found" });
    }
}
