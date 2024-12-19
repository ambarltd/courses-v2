using System.Collections.Generic;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Domain.Enrollment.Queries;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Enrollment.Query;

[ApiController]
[Route("api/v1/credit_card/enrollment")]
public class EnrollmentQueryController : ControllerBase
{
    private readonly IQueryHandler<GetUserEnrollmentsQuery, List<EnrollmentListItemDto>> _queryHandler;
    private readonly ILogger<EnrollmentQueryController> _logger;

    public EnrollmentQueryController(
        IQueryHandler<GetUserEnrollmentsQuery, List<EnrollmentListItemDto>> queryHandler,
        ILogger<EnrollmentQueryController> logger)
    {
        _queryHandler = queryHandler;
        _logger = logger;
    }

    [HttpPost("list-enrollments")]
    public async Task<ActionResult<List<EnrollmentListItemDto>>> ListEnrollments()
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
            var result = await _queryHandler.Handle(query);
            
            _logger.LogInformation(
                "Successfully retrieved {Count} enrollments - Session: {SessionToken}",
                result.Count,
                maskedToken);
            
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
}
