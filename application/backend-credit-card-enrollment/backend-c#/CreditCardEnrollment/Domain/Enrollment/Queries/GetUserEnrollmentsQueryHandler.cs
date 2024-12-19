using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Common.Services;
using CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

namespace CreditCardEnrollment.Domain.Enrollment.Queries;

public class GetUserEnrollmentsQueryHandler(
    IMongoTransactionalProjectionOperator mongoOperator,
    ISessionService sessionService,
    IEnrollmentListRepository enrollmentRepository,
    IProductNameRepository productNameRepository)
    : QueryHandler<GetUserEnrollmentsQuery, List<EnrollmentListItemDto>>(mongoOperator)
{
    public override async Task<List<EnrollmentListItemDto>> Handle(GetUserEnrollmentsQuery query)
    {
        return await MongoOperator.ExecuteInTransaction(async () =>
        {
            var userId = sessionService.GetAuthenticatedUserId(query.SessionToken);
            var enrollments = await enrollmentRepository.FindByUserId(userId);
            var result = new List<EnrollmentListItemDto>();

            foreach (var enrollment in enrollments)
            {
                var product = await productNameRepository.FindById(enrollment.ProductId);
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
