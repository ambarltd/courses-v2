package cloud.ambar.product.enrollment.controllers;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.projection.ProjectionController;
import cloud.ambar.product.enrollment.projection.service.EnrollmentCardProductProjectionService;
import cloud.ambar.product.enrollment.projection.service.EnrollmentProjectionService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card_enrollment/enrollment/projection")
public class EnrollmentProjectionController extends ProjectionController {

    private final EnrollmentCardProductProjectionService cardProductProjectionService;

    private final EnrollmentProjectionService enrollmentProjectionService;

    @PostMapping(value = "/card_products",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleCardProductEvent(AmbarEvent ambarEvent) {
        return processEvent(ambarEvent, cardProductProjectionService);
    }

    @PostMapping(value = "/enrollments",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleEnrollmentEvent(AmbarEvent ambarEvent) {
        return processEvent(ambarEvent, enrollmentProjectionService);
    }
}
