using CreditCardEnrollment.Common.Events;

namespace CreditCardEnrollment.Domain.Product.Events;

public class ProductDefined : CreationEvent
{
    public required string Name { get; init; }
    public required int InterestInBasisPoints { get; init; }
    public required int AnnualFeeInCents { get; init; }
    public required string PaymentCycle { get; init; }
    public required int CreditLimitInCents { get; init; }
    public required int MaxBalanceTransferAllowedInCents { get; init; }
    public required string Reward { get; init; }
    public required string CardBackgroundHex { get; init; }
}
