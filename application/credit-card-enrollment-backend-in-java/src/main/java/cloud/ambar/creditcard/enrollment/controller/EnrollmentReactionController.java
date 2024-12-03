package cloud.ambar.creditcard.enrollment.controller;

import cloud.ambar.common.ambar.httprequest.AmbarHttpRequest;
import cloud.ambar.common.reaction.ReactionController;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.creditcard.enrollment.projection.enrollmentlist.EnrollmentListProjectionHandler;
import cloud.ambar.creditcard.enrollment.projection.isproductactive.IsProductActiveProjectionHandler;
import cloud.ambar.creditcard.enrollment.reaction.ReviewEnrollmentReactionHandler;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.MediaType;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/credit_card/enrollment/reaction")
public class EnrollmentReactionController extends ReactionController {
    private final ReviewEnrollmentReactionHandler reviewEnrollmentReactionHandler;

    public EnrollmentReactionController(
            Deserializer deserializer,
            ReviewEnrollmentReactionHandler reviewEnrollmentReactionHandler) {
        super(deserializer);
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
