using MediatR;

namespace CreditCardEnrollment.Application.Commands.DefineProduct;

public record DefineProductCommand(
    string Name,
    int InterestInBasisPoints,
    int AnnualFeeInCents,
    string PaymentCycle,
    int CreditLimitInCents,
    int MaxBalanceTransferAllowedInCents,
    string Reward,
    string CardBackgroundHex) : IRequest<string>;
