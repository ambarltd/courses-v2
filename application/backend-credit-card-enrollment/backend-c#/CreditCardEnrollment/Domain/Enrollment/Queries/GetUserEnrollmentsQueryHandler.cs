using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Common.Query;
using CreditCardEnrollment.Common.Services;
using CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Enrollment.Queries;

public class GetUserEnrollmentsQueryHandler : QueryHandler<GetUserEnrollmentsQuery, List<EnrollmentListItemDto>>
{
    private readonly IMongoTransactionalProjectionOperator _mongoOperator;
    private readonly ISessionService _sessionService;
    private readonly IEnrollmentListRepository _enrollmentRepository;
    private readonly IProductNameRepository _productNameRepository;
    private readonly ILogger<GetUserEnrollmentsQueryHandler> _logger;

    public GetUserEnrollmentsQueryHandler(
        IMongoTransactionalProjectionOperator mongoOperator,
        ISessionService sessionService,
        IEnrollmentListRepository enrollmentRepository,
        IProductNameRepository productNameRepository,
        ILogger<GetUserEnrollmentsQueryHandler> logger)
        : base(mongoOperator)
    {
        _mongoOperator = mongoOperator;
        _sessionService = sessionService;
        _enrollmentRepository = enrollmentRepository;
        _productNameRepository = productNameRepository;
        _logger = logger;
    }

    public override async Task<List<EnrollmentListItemDto>> Handle(GetUserEnrollmentsQuery query)
    {
        _logger.LogInformation("Processing GetUserEnrollments query");

        return await _mongoOperator.ExecuteInTransaction(async () =>
        {
            var userId = _sessionService.GetAuthenticatedUserId(query.SessionToken);
            _logger.LogDebug("Fetching enrollments for user {UserId}", userId);

            var enrollments = await _enrollmentRepository.FindByUserId(userId);
            _logger.LogInformation("Found {Count} enrollments for user {UserId}", enrollments.Count, userId);

            var result = new List<EnrollmentListItemDto>();

            foreach (var enrollment in enrollments)
            {
                _logger.LogDebug(
                    "Processing enrollment {EnrollmentId} for product {ProductId}",
                    enrollment.Id,
                    enrollment.ProductId);

                var product = await _productNameRepository.FindById(enrollment.ProductId);
                if (product == null)
                {
                    _logger.LogWarning(
                        "Product {ProductId} not found for enrollment {EnrollmentId}",
                        enrollment.ProductId,
                        enrollment.Id);
                }

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

            _logger.LogInformation(
                "Successfully processed GetUserEnrollments query for user {UserId}. Returning {Count} enrollments",
                userId,
                result.Count);

            return result;
        });
    }
}
