using CreditCardEnrollment.Application.Commands.DefineProduct;
using CreditCardEnrollment.Application.Commands.ActivateProduct;
using CreditCardEnrollment.Application.Commands.DeactivateProduct;
using MediatR;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ProductController : ControllerBase
{
    private readonly IMediator _mediator;

    public ProductController(IMediator mediator)
    {
        _mediator = mediator;
    }

    [HttpPost]
    public async Task<IActionResult> DefineProduct([FromBody] DefineProductDto request)
    {
        try
        {
            var command = new DefineProductCommand(
                request.Name,
                request.InterestInBasisPoints,
                request.AnnualFeeInCents,
                request.PaymentCycle,
                request.CreditLimitInCents,
                request.MaxBalanceTransferAllowedInCents,
                request.Reward,
                request.CardBackgroundHex);

            var productId = await _mediator.Send(command);
            return Ok(new { productId });
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new { error = ex.Message });
        }
    }

    [HttpPost("{productId}/activate")]
    public async Task<IActionResult> ActivateProduct(string productId)
    {
        try
        {
            var command = new ActivateProductCommand(productId);
            await _mediator.Send(command);
            return Ok();
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new { error = ex.Message });
        }
    }

    [HttpPost("{productId}/deactivate")]
    public async Task<IActionResult> DeactivateProduct(string productId)
    {
        try
        {
            var command = new DeactivateProductCommand(productId);
            await _mediator.Send(command);
            return Ok();
        }
        catch (InvalidOperationException ex)
        {
            return BadRequest(new { error = ex.Message });
        }
    }
}

public record DefineProductDto(
    string Name,
    int InterestInBasisPoints,
    int AnnualFeeInCents,
    string PaymentCycle,
    int CreditLimitInCents,
    int MaxBalanceTransferAllowedInCents,
    string Reward,
    string CardBackgroundHex);
