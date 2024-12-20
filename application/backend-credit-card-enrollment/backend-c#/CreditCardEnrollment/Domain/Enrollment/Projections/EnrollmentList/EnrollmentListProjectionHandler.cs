using CreditCardEnrollment.Common.Events;
using CreditCardEnrollment.Common.Projection;
using CreditCardEnrollment.Domain.Enrollment.Events;
using CreditCardEnrollment.Domain.Product.Events;
using Microsoft.Extensions.Logging;

namespace CreditCardEnrollment.Domain.Enrollment.Projections.EnrollmentList;

public class EnrollmentListProjectionHandler : ProjectionHandler
{
    private readonly IEnrollmentListRepository _enrollmentRepository;
    private readonly IProductNameRepository _productNameRepository;
    private readonly ILogger<EnrollmentListProjectionHandler> _logger;

    public EnrollmentListProjectionHandler(
        IEnrollmentListRepository enrollmentRepository,
        IProductNameRepository productNameRepository,
        ILogger<EnrollmentListProjectionHandler> logger)
    {
        _enrollmentRepository = enrollmentRepository;
        _productNameRepository = productNameRepository;
        _logger = logger;
    }

    protected override async void Project(Event @event)
    {
        _logger.LogInformation(
            "Processing projection for event type {EventType} with ID {EventId}",
            @event.GetType().Name,
            @event.EventId);

        try
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
                default:
                    _logger.LogDebug("Ignoring unhandled event type: {EventType}", @event.GetType().Name);
                    break;
            }
        }
        catch (Exception ex)
        {
            _logger.LogError(
                ex,
                "Error processing projection for event {EventType} with ID {EventId}",
                @event.GetType().Name,
                @event.EventId);
            throw;
        }
    }

    private async Task HandleProductDefined(ProductDefined @event)
    {
        _logger.LogInformation(
            "Handling ProductDefined event. ProductId: {ProductId}, Name: {ProductName}",
            @event.AggregateId,
            @event.Name);

        await _productNameRepository.Save(new ProductName
        {
            Id = @event.AggregateId,
            Name = @event.Name
        });

        _logger.LogDebug("Successfully saved product name for ProductId: {ProductId}", @event.AggregateId);
    }

    private async Task HandleEnrollmentRequested(EnrollmentRequested @event)
    {
        _logger.LogInformation(
            "Handling EnrollmentRequested event. EnrollmentId: {EnrollmentId}, UserId: {UserId}, ProductId: {ProductId}",
            @event.AggregateId,
            @event.UserId,
            @event.ProductId);

        await _enrollmentRepository.Save(new EnrollmentListItem
        {
            Id = @event.AggregateId,
            UserId = @event.UserId,
            ProductId = @event.ProductId,
            RequestedDate = @event.RecordedOn,
            Status = EnrollmentStatus.Requested.ToString(),
            StatusReason = string.Empty
        });

        _logger.LogDebug("Successfully saved enrollment request for EnrollmentId: {EnrollmentId}", @event.AggregateId);
    }

    private async Task HandleEnrollmentAccepted(EnrollmentAccepted @event)
    {
        _logger.LogInformation(
            "Handling EnrollmentAccepted event. EnrollmentId: {EnrollmentId}",
            @event.AggregateId);

        var enrollment = await _enrollmentRepository.FindById(@event.AggregateId);
        if (enrollment == null)
        {
            _logger.LogWarning(
                "Enrollment not found for EnrollmentAccepted event. EnrollmentId: {EnrollmentId}",
                @event.AggregateId);
            return;
        }

        enrollment.Status = EnrollmentStatus.Accepted.ToString();
        enrollment.ReviewedOn = @event.RecordedOn;
        enrollment.StatusReason = @event.ReasonDescription;

        await _enrollmentRepository.Save(enrollment);
        _logger.LogDebug("Successfully updated enrollment status to Accepted. EnrollmentId: {EnrollmentId}", @event.AggregateId);
    }

    private async Task HandleEnrollmentDeclined(EnrollmentDeclined @event)
    {
        _logger.LogInformation(
            "Handling EnrollmentDeclined event. EnrollmentId: {EnrollmentId}, Reason: {Reason}",
            @event.AggregateId,
            @event.Reason);

        var enrollment = await _enrollmentRepository.FindById(@event.AggregateId);
        if (enrollment == null)
        {
            _logger.LogWarning(
                "Enrollment not found for EnrollmentDeclined event. EnrollmentId: {EnrollmentId}",
                @event.AggregateId);
            return;
        }

        enrollment.Status = EnrollmentStatus.Declined.ToString();
        enrollment.ReviewedOn = @event.RecordedOn;
        enrollment.StatusReason = @event.Reason;

        await _enrollmentRepository.Save(enrollment);
        _logger.LogDebug("Successfully updated enrollment status to Declined. EnrollmentId: {EnrollmentId}", @event.AggregateId);
    }
}
