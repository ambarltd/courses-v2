using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.CreditCard.Enrollment.Aggregate;
using CreditCardEnrollment.CreditCard.Enrollment.Event;
using CreditCardEnrollment.CreditCard.Product.Event;

namespace CreditCardEnrollment.CreditCard.Enrollment.Projection.EnrollmentList;

public class EnrollmentListProjectionHandler : ProjectionHandler {
    private readonly EnrollmentRepository _enrollmentRepository;
    private readonly ProductNameRepository _productNameRepository;

    public EnrollmentListProjectionHandler(
        EnrollmentRepository enrollmentRepository,
        ProductNameRepository productNameRepository) {
        _enrollmentRepository = enrollmentRepository;
        _productNameRepository = productNameRepository;
    }

    public override void Project(Common.Event.Event @event) {
        switch (@event) {
            case ProductDefined productDefined:
                _productNameRepository.Save(new ProductName {
                    Id = productDefined.AggregateId,
                    Name = productDefined.Name
                });
                break;

            case EnrollmentRequested enrollmentRequested:
                _enrollmentRepository.Save(new Enrollment {
                    Id = enrollmentRequested.AggregateId,
                    UserId = enrollmentRequested.UserId,
                    ProductId = enrollmentRequested.ProductId,
                    RequestedDate = enrollmentRequested.RecordedOn,
                    Status = EnrollmentStatus.Requested.ToString()
                });
                break;

            case EnrollmentAccepted enrollmentAccepted:
                var acceptedEnrollment = _enrollmentRepository.FindOneById(enrollmentAccepted.AggregateId) ?? throw new InvalidOperationException();
                acceptedEnrollment.Status = EnrollmentStatus.Accepted.ToString();
                acceptedEnrollment.ReviewedOn = enrollmentAccepted.RecordedOn;
                acceptedEnrollment.StatusReason = enrollmentAccepted.ReasonDescription;
                _enrollmentRepository.Save(acceptedEnrollment);
                break;

            case EnrollmentDeclined enrollmentDeclined:
                var declinedEnrollment = _enrollmentRepository.FindOneById(enrollmentDeclined.AggregateId) ?? throw new InvalidOperationException();
                declinedEnrollment.Status = EnrollmentStatus.Declined.ToString();
                declinedEnrollment.ReviewedOn = enrollmentDeclined.RecordedOn;
                declinedEnrollment.StatusReason = enrollmentDeclined.ReasonDescription;
                _enrollmentRepository.Save(declinedEnrollment);
                break;
        }
    }
}