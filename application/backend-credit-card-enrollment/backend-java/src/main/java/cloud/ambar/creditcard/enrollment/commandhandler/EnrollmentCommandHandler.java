package cloud.ambar.creditcard.enrollment.commandhandler;

import cloud.ambar.common.commandhandler.CommandHandler;
import cloud.ambar.common.eventstore.EventStore;
import cloud.ambar.creditcard.enrollment.event.EnrollmentRequested;
import cloud.ambar.creditcard.enrollment.exception.InactiveProductException;
import cloud.ambar.creditcard.enrollment.projection.isproductactive.IsProductActive;
import org.apache.logging.log4j.LogManager;
import org.apache.logging.log4j.Logger;
import org.springframework.stereotype.Service;
import org.springframework.web.context.annotation.RequestScope;

import java.time.Instant;

import static cloud.ambar.common.util.IdGenerator.generateRandomId;

@Service
@RequestScope
public class EnrollmentCommandHandler extends CommandHandler {
    private static final Logger log = LogManager.getLogger(EnrollmentCommandHandler.class);

    private final IsProductActive isProductActive;

    public EnrollmentCommandHandler(EventStore eventStore, IsProductActive isProductActive) {
        super(eventStore);
        this.isProductActive = isProductActive;
    }

    public void handle(final RequestEnrollmentCommand command) {
        log.info("Handling enrollment request for user: {}, product: {}", command.getUserId(), command.getProductId());
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
