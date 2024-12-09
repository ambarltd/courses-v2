package cloud.ambar.creditcard.enrollment.reaction;

import cloud.ambar.common.ambar.AmbarHttpRequest;
import cloud.ambar.common.reaction.ReactionController;
import cloud.ambar.common.serializedevent.Deserializer;
import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
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
            PostgresTransactionalEventStore postgresTransactionalEventStore,
            MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
            Deserializer deserializer,
            ReviewEnrollmentReactionHandler reviewEnrollmentReactionHandler) {
        super(postgresTransactionalEventStore, mongoTransactionalProjectionOperator, deserializer);
        this.reviewEnrollmentReactionHandler = reviewEnrollmentReactionHandler;
    }

    @PostMapping(value = "/review_enrollment",
            consumes = MediaType.APPLICATION_JSON_VALUE,
            produces = MediaType.APPLICATION_JSON_VALUE)
    public String reactWithReviewEnrollment(
            @Valid @RequestBody AmbarHttpRequest request
    ) {
        return processReactionHttpRequest(request, reviewEnrollmentReactionHandler);
    }
}
