package cloud.ambar.product.enrollment.controllers;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.projection.ProjectionController;
import cloud.ambar.product.enrollment.projection.service.EnrollmentCardProductProjectionService;
import cloud.ambar.product.enrollment.projection.service.EnrollmentProjectionService;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.ObjectMapper;
import lombok.RequiredArgsConstructor;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card_enrollment/enrollment/projection")
public class EnrollmentProjectionController extends ProjectionController {
    private static final Logger log = LogManager.getLogger(EnrollmentProjectionController.class);

    private final EnrollmentCardProductProjectionService cardProductProjectionService;

    private final EnrollmentProjectionService enrollmentProjectionService;

    private final ObjectMapper objectMapper;

    @PostMapping(value = "/card_products",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleCardProductEvent(@RequestBody String event) throws JsonProcessingException {
        log.info("Handling Enrollments Card Product Projection");
        final AmbarEvent ambarEvent = objectMapper.readValue(event, AmbarEvent.class);
        return processEvent(ambarEvent, cardProductProjectionService);
    }

    @PostMapping(value = "/enrollments",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleEnrollmentEvent(@RequestBody String event) throws JsonProcessingException {
        log.info("Handling Enrollments Projection");
        final AmbarEvent ambarEvent = objectMapper.readValue(event, AmbarEvent.class);
        return processEvent(ambarEvent, enrollmentProjectionService);
    }
}
