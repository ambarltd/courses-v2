package cloud.ambar.product.enrollment.controllers;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.reaction.ReactionController;
import cloud.ambar.product.enrollment.reaction.service.CardProductReactionService;
import cloud.ambar.product.enrollment.reaction.service.EnrollmentReactionService;
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
@RequestMapping("/api/v1/credit_card_enrollment/enrollment/reaction")
public class EnrollmentReactionController extends ReactionController {
    private static final Logger log = LogManager.getLogger(EnrollmentReactionController.class);

    private final EnrollmentReactionService enrollmentReactionService;

    private final CardProductReactionService cardProductReactionService;

    private final ObjectMapper objectMapper;

    @PostMapping(value = "/card_products",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleCardProductEvent(@RequestBody String event) throws JsonProcessingException {
        log.info("Handling CardProduct Reaction");
        final AmbarEvent ambarEvent = objectMapper.readValue(event, AmbarEvent.class);
        return processEvent(ambarEvent, cardProductReactionService);
    }

    @PostMapping(value = "/enrollments",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleEnrollmentEvent(@RequestBody String event) throws JsonProcessingException {
        log.info("Handling Enrollments Reaction");
        final AmbarEvent ambarEvent = objectMapper.readValue(event, AmbarEvent.class);
        return processEvent(ambarEvent, enrollmentReactionService);
    }
}
