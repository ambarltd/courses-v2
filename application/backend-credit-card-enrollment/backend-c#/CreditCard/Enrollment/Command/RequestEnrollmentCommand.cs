namespace CreditCardEnrollment.CreditCard.Enrollment.Command;

public class RequestEnrollmentCommand : Common.Command.Command {
    public required string SessionToken { get; init; }
    public required string ProductId { get; init; }
    public required int AnnualIncomeInCents { get; init; }
}