using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Enrollment.Events;
using CreditCardEnrollment.Domain.Product.Events;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

public class EnrollmentListProjectionHandler(
    IEnrollmentListRepository enrollmentRepository,
    IProductNameRepository productNameRepository)
    : ProjectionHandler
{
    protected override async void Project(Event @event)
    {
        switch (@event)
        {
            case ProductDefined productDefined:
                await HandleProductDefined(productDefined);
                break;
            case EnrollmentRequested enrollmentRequested:
                await HandleEnrollmentRequested(enrollmentRequested);
                break;
            case EnrollmentAccepted enrollmentAccepted:
                await HandleEnrollmentAccepted(enrollmentAccepted);
                break;
            case EnrollmentDeclined enrollmentDeclined:
                await HandleEnrollmentDeclined(enrollmentDeclined);
                break;
        }
    }

    private async Task HandleProductDefined(ProductDefined @event)
    {
        await productNameRepository.Save(new ProductName
        {
            Id = @event.AggregateId,
            Name = @event.Name
        });
    }

    private async Task HandleEnrollmentRequested(EnrollmentRequested @event)
    {
        await enrollmentRepository.Save(new EnrollmentListItem
        {
            Id = @event.AggregateId,
            UserId = @event.UserId,
            ProductId = @event.ProductId,
            RequestedDate = @event.RecordedOn,
            Status = EnrollmentStatus.Requested.ToString(),
            StatusReason = string.Empty
        });
    }

    private async Task HandleEnrollmentAccepted(EnrollmentAccepted @event)
    {
        var enrollment = await enrollmentRepository.FindById(@event.AggregateId);
        if (enrollment == null) return;

        enrollment.Status = EnrollmentStatus.Accepted.ToString();
        enrollment.ReviewedOn = @event.RecordedOn;
        enrollment.StatusReason = @event.ReasonDescription;

        await enrollmentRepository.Save(enrollment);
    }

    private async Task HandleEnrollmentDeclined(EnrollmentDeclined @event)
    {
        var enrollment = await enrollmentRepository.FindById(@event.AggregateId);
        if (enrollment == null) return;

        enrollment.Status = EnrollmentStatus.Declined.ToString();
        enrollment.ReviewedOn = @event.RecordedOn;
        enrollment.StatusReason = @event.Reason;

        await enrollmentRepository.Save(enrollment);
    }
}
