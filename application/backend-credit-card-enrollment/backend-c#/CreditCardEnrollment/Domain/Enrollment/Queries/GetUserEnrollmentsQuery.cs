using CreditCardEnrollment.Common.Query;

namespace CreditCardEnrollment.Domain.Enrollment.Queries;

public class GetUserEnrollmentsQuery(string sessionToken) : IQuery<List<EnrollmentListItemDto>>
{
    public string SessionToken { get; } = sessionToken;
}

public class EnrollmentListItemDto
{
    public required string Id { get; set; }
    public required string ProductName { get; set; }
    public required string Status { get; set; }
    public required string StatusReason { get; set; }
    public DateTime RequestedDate { get; set; }
    public DateTime? ReviewedOn { get; set; }
}
