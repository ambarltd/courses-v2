using CreditCardEnrollment.Common.Query;

namespace CreditCardEnrollment.Application.Queries.GetUserEnrollments;

public class GetUserEnrollmentsQuery : IQuery<List<EnrollmentListItemDto>>
{
    public string SessionToken { get; }

    public GetUserEnrollmentsQuery(string sessionToken)
    {
        SessionToken = sessionToken;
    }
}

public class EnrollmentListItemDto
{
    public string Id { get; set; }
    public string ProductName { get; set; }
    public string Status { get; set; }
    public string StatusReason { get; set; }
    public DateTime RequestedDate { get; set; }
    public DateTime? ReviewedOn { get; set; }
}
