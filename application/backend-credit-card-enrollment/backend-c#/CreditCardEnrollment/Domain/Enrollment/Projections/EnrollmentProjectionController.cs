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
public class EnrollmentProjectionController(
    IMongoTransactionalProjectionOperator mongoOperator,
    IDeserializer deserializer,
    IMongoDatabase database,
    EnrollmentListProjectionHandler enrollmentListHandler,
    ProductActiveStatusProjectionHandler productActiveStatusHandler,
    ILogger<EnrollmentProjectionController> logger)
    : ProjectionController(mongoOperator, deserializer, database, logger)
{
    [HttpPost("enrollment_list")]
    [Consumes("application/json")]
    [Produces("application/json")]
    public async Task<IActionResult> ProjectEnrollmentList([FromBody] AmbarHttpRequest request)
    {
        logger.LogInformation(
            "Received enrollment_list projection request. RequestId: {RequestId}, ContentLength: {ContentLength}",
            HttpContext.TraceIdentifier,
            request.SerializedEvent.Length
        );

        try
        {
            var result = await ProcessProjectionHttpRequest(request, enrollmentListHandler, "CreditCard_Enrollment_EnrollmentList");
            
            logger.LogInformation(
                "Completed enrollment_list projection request. RequestId: {RequestId}, StatusCode: {StatusCode}",
                HttpContext.TraceIdentifier,
                (result as ObjectResult)?.StatusCode ?? (result as StatusCodeResult)?.StatusCode
            );
            
            return result;
        }
        catch (Exception ex)
        {
            logger.LogError(
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
        logger.LogInformation(
            "Received is_card_product_active projection request. RequestId: {RequestId}, ContentLength: {ContentLength}",
            HttpContext.TraceIdentifier,
            request.SerializedEvent.Length
        );

        try
        {
            var result = await ProcessProjectionHttpRequest(request, productActiveStatusHandler, "CreditCard_Enrollment_ProductActiveStatus");
            
            logger.LogInformation(
                "Completed is_card_product_active projection request. RequestId: {RequestId}, StatusCode: {StatusCode}",
                HttpContext.TraceIdentifier,
                (result as ObjectResult)?.StatusCode ?? (result as StatusCodeResult)?.StatusCode
            );
            
            return result;
        }
        catch (Exception ex)
        {
            logger.LogError(
                ex,
                "Error processing is_card_product_active projection request. RequestId: {RequestId}",
                HttpContext.TraceIdentifier
            );
            throw; // Let the framework handle the exception
        }
    }
}
