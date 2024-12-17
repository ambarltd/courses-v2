using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Domain.Product;
using CreditCardEnrollment.Domain.Product.Events;
using MediatR;

namespace CreditCardEnrollment.Application.Commands.ActivateProduct;

public class ActivateProductCommandHandler : IRequestHandler<ActivateProductCommand, Unit>
{
    private readonly PostgresEventStore _eventStore;

    public ActivateProductCommandHandler(PostgresEventStore eventStore)
    {
        _eventStore = eventStore;
    }

    public async Task<Unit> Handle(ActivateProductCommand command, CancellationToken cancellationToken)
    {
        // Load the product aggregate from its event history
        var events = await _eventStore.GetEventsForAggregateAsync(command.ProductId);
        var product = new Product();
        product.LoadFromHistory(events);

        // Create and save the activation event
        var eventId = Guid.NewGuid().ToString();
        var activatedEvent = new ProductActivated
        {
            EventId = eventId,
            AggregateId = command.ProductId,
            AggregateVersion = product.Version + 1,
            CorrelationId = eventId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow
        };

        await _eventStore.SaveEventAsync(activatedEvent);

        return Unit.Value;
    }
}
