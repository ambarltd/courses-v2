using CreditCardEnrollment.Common.Command;
using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Common.SessionAuth;
using CreditCardEnrollment.Common.Util;
using CreditCardEnrollment.CreditCard.Enrollment.Event;
using CreditCardEnrollment.CreditCard.Enrollment.Projection.IsProductActive;

namespace CreditCardEnrollment.CreditCard.Enrollment.Command;

public class RequestEnrollmentCommandHandler : CommandHandler {
    private readonly SessionService _sessionService;
    private readonly IsProductActive _isProductActive;

    public RequestEnrollmentCommandHandler(
        PostgresTransactionalEventStore postgresTransactionalEventStore,
        SessionService sessionService,
        IsProductActive isProductActive) : base(postgresTransactionalEventStore) {
        _sessionService = sessionService;
        _isProductActive = isProductActive;
    }

    public override void HandleCommand(Common.Command.Command command) {
        if (command is RequestEnrollmentCommand enrollmentCommand) {
            HandleRequestEnrollment(enrollmentCommand);
        } else {
            throw new ArgumentException($"Unsupported command type: {command.GetType().Name}");
        }
    }

    private void HandleRequestEnrollment(RequestEnrollmentCommand command) {
        var userId = _sessionService.AuthenticatedUserIdFromSessionToken(command.SessionToken);

        if (!_isProductActive.IsProductActiveById(command.ProductId)) {
            throw new Exception("Product is inactive and not eligible for enrollment request.");
        }

        var eventId = IdGenerator.GenerateRandomId();
        var aggregateId = IdGenerator.GenerateRandomId();
        var enrollmentRequested = new EnrollmentRequested {
            EventId = eventId,
            AggregateId = aggregateId,
            AggregateVersion = 1,
            CorrelationId = eventId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            UserId = userId,
            ProductId = command.ProductId,
            AnnualIncomeInCents = command.AnnualIncomeInCents
        };

        _postgresTransactionalEventStore.SaveEvent(enrollmentRequested);
    }
}