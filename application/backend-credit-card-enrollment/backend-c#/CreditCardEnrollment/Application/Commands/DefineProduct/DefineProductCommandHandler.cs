using CreditCardEnrollment.Common.EventStore;
using CreditCardEnrollment.Domain.Product.Events;
using MediatR;

namespace CreditCardEnrollment.Application.Commands.DefineProduct;

public class DefineProductCommandHandler : IRequestHandler<DefineProductCommand, string>
{
    private readonly PostgresEventStore _eventStore;

    public DefineProductCommandHandler(PostgresEventStore eventStore)
    {
        _eventStore = eventStore;
    }

    public async Task<string> Handle(DefineProductCommand command, CancellationToken cancellationToken)
    {
        var eventId = Guid.NewGuid().ToString();
        var aggregateId = Guid.NewGuid().ToString();

        var productDefinedEvent = new ProductDefined
        {
            EventId = eventId,
            AggregateId = aggregateId,
            AggregateVersion = 1,
            CorrelationId = eventId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            Name = command.Name,
            InterestInBasisPoints = command.InterestInBasisPoints,
            AnnualFeeInCents = command.AnnualFeeInCents,
            PaymentCycle = command.PaymentCycle,
            CreditLimitInCents = command.CreditLimitInCents,
            MaxBalanceTransferAllowedInCents = command.MaxBalanceTransferAllowedInCents,
            Reward = command.Reward,
            CardBackgroundHex = command.CardBackgroundHex
        };

        await _eventStore.SaveEventAsync(productDefinedEvent);

        return aggregateId;
    }
}
