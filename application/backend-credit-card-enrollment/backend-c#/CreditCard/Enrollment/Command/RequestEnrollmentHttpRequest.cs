using System.ComponentModel.DataAnnotations;

namespace CreditCardEnrollment.CreditCard.Enrollment.Command;

public class RequestEnrollmentHttpRequest {
    [Required]
    public required string ProductId { get; init; }
    
    [Range(1, int.MaxValue)]
    public required int AnnualIncomeInCents { get; init; }
}