package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.ambar.httprequest.AmbarHttpRequest;
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

@RestController
@RequestMapping("/api/v1/credit_card/enrollment/projection")
public class EnrollmentProjectionController extends ProjectionController {
    private final IsProductActiveProjectionHandler isProductActiveProjectionHandler;

    private final EnrollmentListProjectionHandler enrollmentListProjectionHandler;

    public EnrollmentProjectionController(
            Deserializer deserializer,
            IsProductActiveProjectionHandler isProductActiveProjectionHandler,
            EnrollmentListProjectionHandler enrollmentListProjectionHandler) {
        super(deserializer);
        if (deserializer == null) {
            throw new IllegalArgumentException("Deserializer cannot be null.");
        }
        this.isProductActiveProjectionHandler = isProductActiveProjectionHandler;
        this.enrollmentListProjectionHandler = enrollmentListProjectionHandler;
    }


    @PostMapping(value = "/is_card_product_active",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String projectIsCardProductActive(
            @Valid @RequestBody AmbarHttpRequest request
    ) {
        return processHttpRequest(request, isProductActiveProjectionHandler);
    }
    @PostMapping(value = "/enrollment_list",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String projectEnrollmentList(
            @Valid @RequestBody AmbarHttpRequest request
    ) {
        return processHttpRequest(request, enrollmentListProjectionHandler);
    }
}
