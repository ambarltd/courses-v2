using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.SessionAuth;
using CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

namespace CreditCardEnrollment.CreditCard.Enrollment.Query;

public class GetUserEnrollmentsQueryHandler : QueryHandler {
    private readonly SessionService _sessionService;
    private readonly GetEnrollmentList _getEnrollmentList;

    public GetUserEnrollmentsQueryHandler(
        MongoTransactionalProjectionOperator mongoTransactionalProjectionOperator,
        SessionService sessionService,
        GetEnrollmentList getEnrollmentList) 
        : base(mongoTransactionalProjectionOperator) {
        _sessionService = sessionService;
        _getEnrollmentList = getEnrollmentList;
    }

    public override object HandleQuery(Common.Query.Query query) {
        if (query is GetUserEnrollmentsQuery enrollmentsQuery) {
            return HandleGetUserEnrollments(enrollmentsQuery);
        }
        
        throw new ArgumentException($"Unsupported query type: {query.GetType().Name}");
    }

    private IEnumerable<EnrollmentListItem> HandleGetUserEnrollments(GetUserEnrollmentsQuery query) {
        var userId = _sessionService.AuthenticatedUserIdFromSessionToken(query.SessionToken);
        return _getEnrollmentList.GetList(userId);
    }
}