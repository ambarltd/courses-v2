package cloud.ambar.creditcard.enrollment.commandhandler;

import cloud.ambar.common.commandhandler.Command;
import cloud.ambar.common.commandhandler.CommandHandler;
import cloud.ambar.common.eventstore.PostgresTransactionalEventStore;
import cloud.ambar.common.sessionauth.SessionService;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.enrollment.projection.isproductactive.IsProductActive;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;

import static cloud.ambar.common.util.IdGenerator.generateRandomId;

@Service
@RequestScope
public class RequestEnrollmentCommandHandler extends CommandHandler {
    private final SessionService sessionService;

    private final IsProductActive isProductActive;

    public RequestEnrollmentCommandHandler(
            PostgresTransactionalEventStore postgresTransactionalEventStore,
            SessionService sessionService,
            IsProductActive isProductActive
    ) {
        super(postgresTransactionalEventStore);
        this.sessionService = sessionService;
        this.isProductActive = isProductActive;
    }

    protected void handleCommand(Command command) {
        if (command instanceof RequestEnrollmentCommand) {
            handleRequestEnrollment((RequestEnrollmentCommand) command);
        } else {
            throw new IllegalArgumentException("Unsupported command type: " + command.getClass().getName());
        }
    }

    private void handleRequestEnrollment(final RequestEnrollmentCommand command) {
        String userId = sessionService.authenticatedUserIdFromSessionToken(command.getSessionToken());

        if (!isProductActive.isProductActive(command.getProductId())) {
            throw new RuntimeException("Product is inactive and not eligible for enrollment request.");
        }

        final String eventId = generateRandomId();
        final String aggregateId = generateRandomId();
        final EnrollmentRequested enrollmentRequested = EnrollmentRequested.builder()
                .eventId(eventId)
                .aggregateId(aggregateId)
                .aggregateVersion(1)
                .correlationId(eventId)
                .causationId(eventId)
                .recordedOn(Instant.now())
                .userId(userId)
                .productId(command.getProductId())
                .annualIncomeInCents(command.getAnnualIncomeInCents())
                .build();

        postgresTransactionalEventStore.saveEvent(enrollmentRequested);
    }
}
