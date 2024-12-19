using System.ComponentModel.DataAnnotations;

namespace CreditCardEnrollment.Application.Commands.RequestEnrollment;

public class RequestEnrollmentHttpRequest
{
    [Required]
    public required string ProductId { get; set; }

    [Range(1, int.MaxValue, ErrorMessage = "Annual income must be positive")]
    public int AnnualIncomeInCents { get; set; }
}
