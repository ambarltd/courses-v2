package cloud.ambar.creditcard.enrollment.commandhandler;

import cloud.ambar.common.commandhandler.CommandController;
import cloud.ambar.common.projection.MongoTransactionalProjectionOperator;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.context.annotation.RequestScope;

@RestController
@RequestScope
@RequestMapping("/api/v1/credit_card/enrollment")
public class EnrollmentCommandController extends CommandController {
    private final RequestEnrollmentCommandHandler requestEnrollmentCommandHandler;

    public EnrollmentCommandController(
            PostgresTransactionalEventStore postgresTransactionalEventStore,
            MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
            RequestEnrollmentCommandHandler requestEnrollmentCommandHandler
    ) {
        super(postgresTransactionalEventStore, mongoTransactionalProjectionOperator);
        this.requestEnrollmentCommandHandler = requestEnrollmentCommandHandler;
    }

    @PostMapping("/request-enrollment")
    @ResponseStatus(HttpStatus.OK)
    public void requestEnrollment(
            @RequestHeader("X-With-Session-Token") String sessionToken,
            @Valid @RequestBody RequestEnrollmentHttpRequest request) {
        final RequestEnrollmentCommand command = RequestEnrollmentCommand
                .builder()
                .sessionToken(sessionToken)
                .productId(request.getProductId())
                .annualIncomeInCents(request.getAnnualIncomeInCents())
                .build();

        processCommand(command, requestEnrollmentCommandHandler);
    }
}
