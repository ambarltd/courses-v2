using CreditCardEnrollment.CreditCard.Enrollment.Aggregate;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class GetEnrollmentList {
    private readonly EnrollmentRepository _enrollmentRepository;
    private readonly ProductNameRepository _productNameRepository;

    public GetEnrollmentList(
        EnrollmentRepository enrollmentRepository,
        ProductNameRepository productNameRepository) {
        _enrollmentRepository = enrollmentRepository;
        _productNameRepository = productNameRepository;
    }

    public IEnumerable<EnrollmentListItem> GetList(string userId) {
        return _enrollmentRepository.FindAllByUserId(userId)
            .Select(enrollment => new EnrollmentListItem {
                Id = enrollment.Id,
                UserId = enrollment.UserId,
                ProductId = enrollment.ProductId,
                ProductName = _productNameRepository.FindOneById(enrollment.ProductId)?.Name 
                              ?? throw new InvalidOperationException(),
                RequestedDate = enrollment.RequestedDate,
                Status = enrollment.Status,
                StatusReason = enrollment.StatusReason,
                ReviewedOn = enrollment.ReviewedOn
            });
    }

    public bool IsThereAnyAcceptedEnrollmentForUserAndProduct(string userId, string productId) {
        return _enrollmentRepository.FindAllByUserId(userId)
            .Any(e => e.ProductId == productId && e.Status == EnrollmentStatus.Accepted.ToString());
    }
}