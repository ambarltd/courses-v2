package cloud.ambar.product.enrollment.controllers;

import cloud.ambar.common.ambar.event.AmbarEvent;
import cloud.ambar.common.ambar.response.AmbarResponse;
import cloud.ambar.common.reaction.ReactionController;
import cloud.ambar.product.enrollment.reaction.service.CardProductReactionService;
import cloud.ambar.product.enrollment.reaction.service.EnrollmentReactionService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequiredArgsConstructor
@RequestMapping("/api/v1/credit_card_enrollment/enrollment/reaction")
public class EnrollmentReactionController extends ReactionController {

    private final EnrollmentReactionService enrollmentReactionService;

    private final CardProductReactionService cardProductReactionService;

    @PostMapping(value = "/card_products",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleCardProductEvent(AmbarEvent ambarEvent) {
        return processEvent(ambarEvent, cardProductReactionService);
    }

    @PostMapping(value = "/enrollments",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public AmbarResponse handleEnrollmentEvent(AmbarEvent ambarEvent) {
        return processEvent(ambarEvent, enrollmentReactionService);
    }
}
