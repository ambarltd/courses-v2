using System.Text.Json;
using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;
using CreditCardEnrollment.Domain.Product.Projections.ProductActiveStatus;
using Microsoft.AspNetCore.Mvc;
using MongoDB.Driver;

namespace CreditCardEnrollment.Domain.Enrollment.Projections;

[ApiController]
[Route("api/v1/credit_card/enrollment/projection")]
public class EnrollmentProjectionController : ProjectionController
{
    private readonly EnrollmentListProjectionHandler _enrollmentListHandler;
    private readonly ProductActiveStatusProjectionHandler _productActiveStatusHandler;
    private readonly ILogger<EnrollmentProjectionController> _logger;

    public EnrollmentProjectionController(
        IMongoTransactionalProjectionOperator mongoOperator,
        IDeserializer deserializer,
        IMongoDatabase database,
        EnrollmentListProjectionHandler enrollmentListHandler,
        ProductActiveStatusProjectionHandler productActiveStatusHandler,
        ILogger<EnrollmentProjectionController> logger)
        : base(mongoOperator, deserializer, database, logger)
    {
        _enrollmentListHandler = enrollmentListHandler;
        _productActiveStatusHandler = productActiveStatusHandler;
        _logger = logger;
    }

    [HttpPost("enrollment_list")]
    [Consumes("application/json")]
    [Produces("application/json")]
    public async Task<IActionResult> ProjectEnrollmentList([FromBody] AmbarHttpRequest request)
    {
        _logger.LogInformation(
            "Received enrollment_list projection request. RequestId: {RequestId}, DataSourceId: {DataSourceId}, DataDestinationId: {DataDestinationId}",
            HttpContext.TraceIdentifier,
            request?.DataSourceId,
            request?.DataDestinationId
        );

        if (request?.SerializedEvent == null)
        {
            _logger.LogWarning("Invalid request: SerializedEvent is null. RequestId: {RequestId}", HttpContext.TraceIdentifier);
            return BadRequest(new { error = "SerializedEvent is required" });
        }

        try
        {
            _logger.LogDebug(
                "Processing event. EventId: {EventId}, EventName: {EventName}, AggregateId: {AggregateId}, JsonPayload: {JsonPayload}",
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName,
                request.SerializedEvent.AggregateId,
                request.SerializedEvent.JsonPayload
            );

            var result = await ProcessProjectionHttpRequest(request, _enrollmentListHandler, "CreditCard_Enrollment_EnrollmentList");
            
            _logger.LogInformation(
                "Completed enrollment_list projection request. RequestId: {RequestId}, StatusCode: {StatusCode}",
                HttpContext.TraceIdentifier,
                (result as ObjectResult)?.StatusCode ?? (result as StatusCodeResult)?.StatusCode
            );
            
            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error processing enrollment_list projection request. RequestId: {RequestId}, EventId: {EventId}, EventName: {EventName}",
                HttpContext.TraceIdentifier,
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName
            );
            return StatusCode(500, new { error = "T Internal server error", message = ex.Message });
        }
    }

    [HttpPost("is_card_product_active")]
    [Consumes("application/json")]
    [Produces("application/json")]
    public async Task<IActionResult> ProjectIsCardProductActive([FromBody] AmbarHttpRequest request)
    {
        _logger.LogInformation(
            "Received is_card_product_active projection request. RequestId: {RequestId}, DataSourceId: {DataSourceId}, DataDestinationId: {DataDestinationId}",
            HttpContext.TraceIdentifier,
            request?.DataSourceId,
            request?.DataDestinationId
        );

        if (request?.SerializedEvent == null)
        {
            _logger.LogWarning("Invalid request: SerializedEvent is null. RequestId: {RequestId}", HttpContext.TraceIdentifier);
            return BadRequest(new { error = "SerializedEvent is required" });
        }

        try
        {
            _logger.LogDebug(
                "Processing event. EventId: {EventId}, EventName: {EventName}, AggregateId: {AggregateId}, JsonPayload: {JsonPayload}",
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName,
                request.SerializedEvent.AggregateId,
                request.SerializedEvent.JsonPayload
            );
            
            var result = await ProcessProjectionHttpRequest(request, _productActiveStatusHandler, "CreditCard_Enrollment_ProductActiveStatus");
            
            _logger.LogInformation(
                "Completed is_card_product_active projection request. RequestId: {RequestId}, StatusCode: {StatusCode}",
                HttpContext.TraceIdentifier,
                (result as ObjectResult)?.StatusCode ?? (result as StatusCodeResult)?.StatusCode
            );
            
            return result;
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error processing is_card_product_active projection request. RequestId: {RequestId}, EventId: {EventId}, EventName: {EventName}",
                HttpContext.TraceIdentifier,
                request.SerializedEvent.EventId,
                request.SerializedEvent.EventName
            );
            return StatusCode(500, new { error = "S Internal server error", message = ex.Message });
        }
    }
}
