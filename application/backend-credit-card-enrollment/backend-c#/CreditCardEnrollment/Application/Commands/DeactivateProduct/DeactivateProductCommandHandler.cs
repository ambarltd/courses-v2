using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Domain.Product;
using CreditCardEnrollment.Domain.Product.Events;
using MediatR;

namespace CreditCardEnrollment.Application.Commands.DeactivateProduct;

public class DeactivateProductCommandHandler : IRequestHandler<DeactivateProductCommand, Unit>
{
    private readonly PostgresEventStore _eventStore;

    public DeactivateProductCommandHandler(PostgresEventStore eventStore)
    {
        _eventStore = eventStore;
    }

    public async Task<Unit> Handle(DeactivateProductCommand command, CancellationToken cancellationToken)
    {
        // Load the product aggregate from its event history
        var events = await _eventStore.GetEventsForAggregateAsync(command.ProductId);
        var product = new Product();
        product.LoadFromHistory(events);

        // Create and save the deactivation event
        var eventId = Guid.NewGuid().ToString();
        var deactivatedEvent = new ProductDeactivated
        {
            EventId = eventId,
            AggregateId = command.ProductId,
            AggregateVersion = product.Version + 1,
            CorrelationId = eventId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow
        };

        await _eventStore.SaveEventAsync(deactivatedEvent);

        return Unit.Value;
    }
}
