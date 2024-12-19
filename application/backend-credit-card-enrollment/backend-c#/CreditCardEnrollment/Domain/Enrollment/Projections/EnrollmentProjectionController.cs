using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;
using CreditCardEnrollment.Domain.Enrollment.Projections.IsProductActive;
using Microsoft.AspNetCore.Mvc;
using MongoDB.Driver;

namespace CreditCardEnrollment.Domain.Enrollment.Projections;

[ApiController]
[Route("api/v1/credit_card/enrollment/projection")]
public class EnrollmentProjectionController : ProjectionController
{
    private readonly EnrollmentListProjectionHandler _enrollmentListHandler;
    private readonly IsProductActiveProjectionHandler _isProductActiveHandler;
    private readonly ILogger<EnrollmentProjectionController> _logger;

    public EnrollmentProjectionController(
        IMongoTransactionalProjectionOperator mongoOperator,
        IDeserializer deserializer,
        IMongoDatabase database,
        EnrollmentListProjectionHandler enrollmentListHandler,
        IsProductActiveProjectionHandler isProductActiveHandler,
        ILogger<EnrollmentProjectionController> logger)
        : base(mongoOperator, deserializer, database, logger)
    {
        _enrollmentListHandler = enrollmentListHandler;
        _isProductActiveHandler = isProductActiveHandler;
        _logger = logger;
    }

    [HttpPost("enrollment_list")]
    [Consumes("application/json")]
    [Produces("application/json")]
    public async Task<IActionResult> ProjectEnrollmentList([FromBody] AmbarHttpRequest request)
    {
        _logger.LogInformation(
            "Received enrollment_list projection request. RequestId: {RequestId}, ContentLength: {ContentLength}",
            HttpContext.TraceIdentifier,
            request.SerializedEvent.Length
        );

        try
        {
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
                "Error processing enrollment_list projection request. RequestId: {RequestId}",
                HttpContext.TraceIdentifier
            );
            throw; // Let the framework handle the exception
        }
    }

    [HttpPost("is_card_product_active")]
    [Consumes("application/json")]
    [Produces("application/json")]
    public async Task<IActionResult> ProjectIsCardProductActive([FromBody] AmbarHttpRequest request)
    {
        _logger.LogInformation(
            "Received is_card_product_active projection request. RequestId: {RequestId}, ContentLength: {ContentLength}",
            HttpContext.TraceIdentifier,
            request.SerializedEvent.Length
        );

        try
        {
            var result = await ProcessProjectionHttpRequest(request, _isProductActiveHandler, "CreditCard_Enrollment_IsProductActive");
            
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
                "Error processing is_card_product_active projection request. RequestId: {RequestId}",
                HttpContext.TraceIdentifier
            );
            throw; // Let the framework handle the exception
        }
    }
}
