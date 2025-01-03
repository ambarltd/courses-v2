using CreditCardEnrollment.Common.Ambar;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.SerializedEvent;
using CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;
using CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;
using Microsoft.AspNetCore.Mvc;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection;

[ApiController]
[Route("api/v1/credit_card/enrollment/projection")]
[Produces("application/json")]
[Consumes("application/json")]
public class EnrollmentProjectionController : ProjectionController {
    private readonly IsProductActiveProjectionHandler _isProductActiveProjectionHandler;
    private readonly EnrollmentListProjectionHandler _enrollmentListProjectionHandler;

    public EnrollmentProjectionController(
        MongoTransactionalProjectionOperator mongoOperator,
        Deserializer deserializer,
        IsProductActiveProjectionHandler isProductActiveProjectionHandler,
        EnrollmentListProjectionHandler enrollmentListProjectionHandler)
        : base(mongoOperator, deserializer) {
        _isProductActiveProjectionHandler = isProductActiveProjectionHandler;
        _enrollmentListProjectionHandler = enrollmentListProjectionHandler;
    }

    [HttpPost("is_card_product_active")]
    public IActionResult ProjectIsCardProductActive([FromBody] AmbarHttpRequest request) {
        return new ContentResult {
            Content = ProcessProjectionHttpRequest(
                request, 
                _isProductActiveProjectionHandler, 
                "CreditCard_Enrollment_IsProductActive"),
            ContentType = "application/json",
            StatusCode = 200
        };
    }

    [HttpPost("enrollment_list")]
    public IActionResult ProjectEnrollmentList([FromBody] AmbarHttpRequest request) {
        return new ContentResult {
            Content = ProcessProjectionHttpRequest(
                request, 
                _enrollmentListProjectionHandler, 
                "CreditCard_Enrollment_EnrollmentList"),
            ContentType = "application/json",
            StatusCode = 200
        };
    }
}