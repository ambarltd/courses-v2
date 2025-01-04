namespace CreditCardEnrollment.CreditCard.Product.Event;

public class ProductDefined : Common.Event.Event
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
