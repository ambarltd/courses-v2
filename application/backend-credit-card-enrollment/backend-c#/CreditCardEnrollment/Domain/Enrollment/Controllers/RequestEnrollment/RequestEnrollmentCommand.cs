using MediatR;

namespace CreditCardEnrollment.Application.Commands.RequestEnrollment;

public record RequestEnrollmentCommand(
    string SessionToken,
    string ProductId,
    int AnnualIncomeInCents) : IRequest<string>;
