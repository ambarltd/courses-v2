using System.ComponentModel.DataAnnotations;

namespace CreditCardEnrollment.CreditCard.Enrollment.Query;

public class GetUserEnrollmentsQuery : Common.Query.Query {
    [Required]
    public required string SessionToken { get; init; }
}