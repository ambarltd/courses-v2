 using CreditCardEnrollment.Application.Commands.RequestEnrollment;
using MediatR;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Controllers;

[ApiController]
[Route("api/[controller]")]
public class EnrollmentController : ControllerBase
{
    private readonly IMediator _mediator;

    public EnrollmentController(IMediator mediator)
    {
        _mediator = mediator;
    }

    [HttpPost]
    public async Task<IActionResult> RequestEnrollment([FromBody] RequestEnrollmentDto request)
    {
        try
        {
            var command = new RequestEnrollmentCommand(
                request.SessionToken,
                request.ProductId,
                request.AnnualIncomeInCents);

            var enrollmentId = await _mediator.Send(command);
            return Ok(new { enrollmentId });
        }
        catch (UnauthorizedAccessException)
        {
            return Unauthorized();
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new { error = ex.Message });
        }
    }
}

public record RequestEnrollmentDto(
    string SessionToken,
    string ProductId,
    int AnnualIncomeInCents);
