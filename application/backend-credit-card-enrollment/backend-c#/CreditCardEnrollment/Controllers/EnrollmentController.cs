using CreditCardEnrollment.Application.Commands.RequestEnrollment;
using CreditCardEnrollment.Application.Queries.GetUserEnrollments;
using MediatR;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Controllers;

[ApiController]
[Route("[controller]")]
public class EnrollmentController : ControllerBase
{
    private readonly IMediator _mediator;

    public EnrollmentController(IMediator mediator)
    {
        _mediator = mediator;
    }

    [HttpPost("request")]
    public async Task<IActionResult> RequestEnrollment([FromBody] RequestEnrollmentCommand command)
    {
        await _mediator.Send(command);
        return Ok();
    }

    [HttpGet]
    public async Task<ActionResult<List<EnrollmentListItemDto>>> GetUserEnrollments()
    {
        var sessionToken = Request.Headers["Authorization"].ToString();
        var query = new GetUserEnrollmentsQuery(sessionToken);
        var result = await _mediator.Send(query);
        return Ok(result);
    }
}
