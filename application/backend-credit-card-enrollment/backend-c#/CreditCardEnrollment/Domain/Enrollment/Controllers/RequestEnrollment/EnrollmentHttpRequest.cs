using System.ComponentModel.DataAnnotations;

namespace CreditCardEnrollment.Domain.Enrollment.Controllers.RequestEnrollment;

public class EnrollmentHttpRequest
{
    [Required]
    public required string ProductId { get; set; }

    [Range(1, int.MaxValue, ErrorMessage = "Annual income must be positive")]
    public int AnnualIncomeInCents { get; set; }
}
