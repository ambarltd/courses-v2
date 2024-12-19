using MediatR;

namespace CreditCardEnrollment.Domain.Enrollment.Controllers.RequestEnrollment;

public record RequestEnrollmentCommand(
    string SessionToken,
    string ProductId,
    int AnnualIncomeInCents) : IRequest<string>;
