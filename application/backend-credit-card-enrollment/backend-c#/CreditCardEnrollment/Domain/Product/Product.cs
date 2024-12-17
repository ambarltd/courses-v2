using CreditCardEnrollment.Common.Aggregate;
using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Domain.Product.Events;

namespace CreditCardEnrollment.Domain.Product;

public class Product : Aggregate
{
    public string Name { get; private set; } = string.Empty;
    public int InterestInBasisPoints { get; private set; }
    public int AnnualFeeInCents { get; private set; }
    public string PaymentCycle { get; private set; } = string.Empty;
    public int CreditLimitInCents { get; private set; }
    public int MaxBalanceTransferAllowedInCents { get; private set; }
    public string Reward { get; private set; } = string.Empty;
    public string CardBackgroundHex { get; private set; } = string.Empty;
    public bool IsActive { get; private set; }

    public static Product Define(
        string eventId,
        string aggregateId,
        string correlationId,
        string name,
        int interestInBasisPoints,
        int annualFeeInCents,
        string paymentCycle,
        int creditLimitInCents,
        int maxBalanceTransferAllowedInCents,
        string reward,
        string cardBackgroundHex)
    {
        var product = new Product();
        
        var @event = new ProductDefined
        {
            EventId = eventId,
            AggregateId = aggregateId,
            AggregateVersion = 1,
            CorrelationId = correlationId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow,
            Name = name,
            InterestInBasisPoints = interestInBasisPoints,
            AnnualFeeInCents = annualFeeInCents,
            PaymentCycle = paymentCycle,
            CreditLimitInCents = creditLimitInCents,
            MaxBalanceTransferAllowedInCents = maxBalanceTransferAllowedInCents,
            Reward = reward,
            CardBackgroundHex = cardBackgroundHex
        };

        product.Apply(@event);
        return product;
    }

    public void Activate(string eventId, string correlationId)
    {
        if (IsActive)
            throw new InvalidOperationException("Product is already active");

        var @event = new ProductActivated
        {
            EventId = eventId,
            AggregateId = Id,
            AggregateVersion = Version + 1,
            CorrelationId = correlationId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow
        };

        Apply(@event);
    }

    public void Deactivate(string eventId, string correlationId)
    {
        if (!IsActive)
            throw new InvalidOperationException("Product is already inactive");

        var @event = new ProductDeactivated
        {
            EventId = eventId,
            AggregateId = Id,
            AggregateVersion = Version + 1,
            CorrelationId = correlationId,
            CausationId = eventId,
            RecordedOn = DateTime.UtcNow
        };

        Apply(@event);
    }

    protected override void Apply(Event @event)
    {
        switch (@event)
        {
            case ProductDefined defined:
                Id = defined.AggregateId;
                Name = defined.Name;
                InterestInBasisPoints = defined.InterestInBasisPoints;
                AnnualFeeInCents = defined.AnnualFeeInCents;
                PaymentCycle = defined.PaymentCycle;
                CreditLimitInCents = defined.CreditLimitInCents;
                MaxBalanceTransferAllowedInCents = defined.MaxBalanceTransferAllowedInCents;
                Reward = defined.Reward;
                CardBackgroundHex = defined.CardBackgroundHex;
                IsActive = false;
                break;

            case ProductActivated:
                IsActive = true;
                break;

            case ProductDeactivated:
                IsActive = false;
                break;

            default:
                throw new ArgumentException($"Unknown event type: {@event.GetType().Name}");
        }
    }
}
