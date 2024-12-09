package cloud.ambar.creditcard.enrollment.projection;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import cloud.ambar.common.projection.ProjectionController;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.EnrollmentListProjectionHandler;
import cloud.ambar.creditcard.enrollment.projection.isproductactive.IsProductActiveProjectionHandler;
import jakarta.validation.Valid;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.context.annotation.RequestScope;

@RestController
@RequestScope
@RequestMapping("/api/v1/credit_card/enrollment/projection")
public class EnrollmentProjectionController extends ProjectionController {
    private final IsProductActiveProjectionHandler isProductActiveProjectionHandler;

    private final EnrollmentListProjectionHandler enrollmentListProjectionHandler;

    public EnrollmentProjectionController(
            Deserializer deserializer,
            MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
            IsProductActiveProjectionHandler isProductActiveProjectionHandler,
            EnrollmentListProjectionHandler enrollmentListProjectionHandler) {
        super(mongoTransactionalProjectionOperator, deserializer);
        this.isProductActiveProjectionHandler = isProductActiveProjectionHandler;
        this.enrollmentListProjectionHandler = enrollmentListProjectionHandler;
    }

    @PostMapping(value = "/is_card_product_active",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String projectIsCardProductActive(
            @Valid @RequestBody AmbarHttpRequest request
    ) {
        return processProjectionHttpRequest(request, isProductActiveProjectionHandler, "CreditCard_Enrollment_IsProductActive");
    }
    @PostMapping(value = "/enrollment_list",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String projectEnrollmentList(
            @Valid @RequestBody AmbarHttpRequest request
    ) {
        return processProjectionHttpRequest(request, enrollmentListProjectionHandler, "CreditCard_Enrollment_EnrollmentList");
    }
}
