package cloud.ambar.creditcard.enrollment.commandhandler;

import cloud.ambar.common.commandhandler.Command;
import cloud.ambar.common.commandhandler.CommandHandler;
import cloud.ambar.common.eventstore.EventStore;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.enrollment.exception.InactiveProductException;
import cloud.ambar.creditcard.enrollment.projection.isproductactive.IsProductActive;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;

import static cloud.ambar.common.util.IdGenerator.generateRandomId;

@Service
@RequestScope
public class RequestEnrollmentCommandHandler extends CommandHandler {
    private final IsProductActive isProductActive;

    public RequestEnrollmentCommandHandler(EventStore eventStore, IsProductActive isProductActive) {
        super(eventStore);
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
        if (!isProductActive.isProductActive(command.getProductId())) {
            throw new InactiveProductException();
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
                .userId(command.getUserId())
                .productId(command.getProductId())
                .annualIncomeInCents(command.getAnnualIncomeInCents())
                .build();

        eventStore.saveEvent(enrollmentRequested);
    }
}
