using CreditCardEnrollment.Application.Services;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

namespace CreditCardEnrollment.Application.Queries.GetUserEnrollments;

public class GetUserEnrollmentsQueryHandler : QueryHandler<GetUserEnrollmentsQuery, List<EnrollmentListItemDto>>
{
    private readonly ISessionService _sessionService;
    private readonly IEnrollmentListRepository _enrollmentRepository;
    private readonly IProductNameRepository _productNameRepository;

    public GetUserEnrollmentsQueryHandler(
        IMongoTransactionalProjectionOperator mongoOperator,
        ISessionService sessionService,
        IEnrollmentListRepository enrollmentRepository,
        IProductNameRepository productNameRepository) 
        : base(mongoOperator)
    {
        _sessionService = sessionService;
        _enrollmentRepository = enrollmentRepository;
        _productNameRepository = productNameRepository;
    }

    public override async Task<List<EnrollmentListItemDto>> Handle(GetUserEnrollmentsQuery query)
    {
        return await MongoOperator.ExecuteInTransaction(async () =>
        {
            var userId = _sessionService.GetAuthenticatedUserId(query.SessionToken);
            var enrollments = await _enrollmentRepository.FindByUserId(userId);
            var result = new List<EnrollmentListItemDto>();

            foreach (var enrollment in enrollments)
            {
                var product = await _productNameRepository.FindById(enrollment.ProductId);
                result.Add(new EnrollmentListItemDto
                {
                    Id = enrollment.Id,
                    ProductName = product?.Name ?? "Unknown Product",
                    Status = enrollment.Status,
                    StatusReason = enrollment.StatusReason,
                    RequestedDate = enrollment.RequestedDate,
                    ReviewedOn = enrollment.ReviewedOn
                });
            }

            return result;
        });
    }
}
