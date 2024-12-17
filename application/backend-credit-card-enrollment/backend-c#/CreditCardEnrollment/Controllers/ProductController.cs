using CreditCardEnrollment.Application.Commands.DefineProduct;
using CreditCardEnrollment.Application.Commands.ActivateProduct;
using CreditCardEnrollment.Application.Commands.DeactivateProduct;
using MediatR;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Controllers;

[ApiController]
[Route("api/[controller]")]
public class ProductController : ControllerBase
{
    private readonly IMediator _mediator;
    private readonly ILogger<ProductController> _logger;

    public ProductController(IMediator mediator, ILogger<ProductController> logger)
    {
        _mediator = mediator;
        _logger = logger;
    }

    [HttpPost]
    public async Task<IActionResult> DefineProduct([FromBody] DefineProductDto request)
    {
        _logger.LogInformation("Received define product request: {@Request}", request);
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
            _logger.LogInformation("Successfully defined product with ID: {ProductId}", productId);
            return Ok(new { productId });
        }
        catch (InvalidOperationException ex)
        {
            _logger.LogError(ex, "Error defining product: {@Request}", request);
            return BadRequest(new { error = ex.Message });
        }
    }

    [HttpPost("{productId}/activate")]
    public async Task<IActionResult> ActivateProduct(string productId)
    {
        _logger.LogInformation("Received activate product request for ID: {ProductId}", productId);
        try
        {
            var command = new ActivateProductCommand(productId);
            await _mediator.Send(command);
            _logger.LogInformation("Successfully activated product: {ProductId}", productId);
            return Ok();
        }
        catch (InvalidOperationException ex)
        {
            _logger.LogError(ex, "Error activating product: {ProductId}", productId);
            return BadRequest(new { error = ex.Message });
        }
    }

    [HttpPost("{productId}/deactivate")]
    public async Task<IActionResult> DeactivateProduct(string productId)
    {
        _logger.LogInformation("Received deactivate product request for ID: {ProductId}", productId);
        try
        {
            var command = new DeactivateProductCommand(productId);
            await _mediator.Send(command);
            _logger.LogInformation("Successfully deactivated product: {ProductId}", productId);
            return Ok();
        }
        catch (InvalidOperationException ex)
        {
            _logger.LogError(ex, "Error deactivating product: {ProductId}", productId);
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
