package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.eventstore.EventStore;
import cloud.ambar.common.reaction.ReactionController;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.creditcard.enrollment.reaction.ReviewEnrollmentReactionHandler;
import jakarta.validation.Valid;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.context.annotation.RequestScope;

@RestController
@RequestScope
@RequestMapping("/api/v1/credit_card/enrollment/reaction")
public class EnrollmentReactionController extends ReactionController {
    private final ReviewEnrollmentReactionHandler reviewEnrollmentReactionHandler;

    public EnrollmentReactionController(
            EventStore eventStore,
            Deserializer deserializer,
            ReviewEnrollmentReactionHandler reviewEnrollmentReactionHandler) {
        super(eventStore, deserializer);
        this.reviewEnrollmentReactionHandler = reviewEnrollmentReactionHandler;
    }

    @PostMapping(value = "/review_enrollment",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String reactWithReviewEnrollment(
            @Valid @RequestBody AmbarHttpRequest request
    ) {
        return processHttpRequest(request, reviewEnrollmentReactionHandler);
    }
}
