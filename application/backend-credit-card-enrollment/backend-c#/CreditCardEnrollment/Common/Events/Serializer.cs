using System.Text.Json;
using System.Text.Json.Nodes;
using CreditCardEnrollment.Domain.Enrollment.Events;
using CreditCardEnrollment.Domain.Product.Events;

namespace CreditCardEnrollment.Common.Events;

public interface ISerializer
{
    SerializedEvent Serialize(Event @event);
}

public class Serializer : ISerializer
{
    public SerializedEvent Serialize(Event @event)
    {
        return new SerializedEvent
        {
            EventId = @event.EventId,
            AggregateId = @event.AggregateId,
            AggregateVersion = @event.AggregateVersion,
            CorrelationId = @event.CorrelationId,
            CausationId = @event.CausationId,
            RecordedOn = FormatDateTime(@event.RecordedOn),
            EventName = DetermineEventName(@event),
            JsonPayload = CreateJsonPayload(@event),
            JsonMetadata = "{}"
        };
    }

    private static string DetermineEventName(Event @event) => @event switch
    {
        EnrollmentRequested => "CreditCard_Enrollment_EnrollmentRequested",
        EnrollmentAccepted => "CreditCard_Enrollment_EnrollmentAccepted",
        EnrollmentDeclined => "CreditCard_Enrollment_EnrollmentDeclined",
        ProductActivated => "CreditCard_Product_ProductActivated",
        ProductDeactivated => "CreditCard_Product_ProductDeactivated",
        ProductDefined => "CreditCard_Product_ProductDefined",
        _ => throw new ArgumentException($"Unknown event type: {@event.GetType().Name}")
    };

    private static string CreateJsonPayload(Event @event)
    {
        var jsonObject = new JsonObject();

        switch (@event)
        {
            case EnrollmentRequested enrollmentRequested:
                jsonObject.Add("annualIncomeInCents", enrollmentRequested.AnnualIncomeInCents);
                jsonObject.Add("productId", enrollmentRequested.ProductId);
                jsonObject.Add("userId", enrollmentRequested.UserId);
                break;

            case EnrollmentAccepted enrollmentAccepted:
                jsonObject.Add("reasonCode", enrollmentAccepted.ReasonCode);
                jsonObject.Add("reasonDescription", enrollmentAccepted.ReasonDescription);
                jsonObject.Add("userId", enrollmentAccepted.UserId);
                jsonObject.Add("productId", enrollmentAccepted.ProductId);
                break;

            case EnrollmentDeclined enrollmentDeclined:
                jsonObject.Add("reasonCode", enrollmentDeclined.ReasonCode);
                jsonObject.Add("reasonDescription", enrollmentDeclined.ReasonDescription);
                jsonObject.Add("userId", enrollmentDeclined.UserId);
                jsonObject.Add("productId", enrollmentDeclined.ProductId);
                break;

            case ProductDefined productDefined:
                jsonObject.Add("name", productDefined.Name);
                jsonObject.Add("interestInBasisPoints", productDefined.InterestInBasisPoints);
                jsonObject.Add("annualFeeInCents", productDefined.AnnualFeeInCents);
                jsonObject.Add("paymentCycle", productDefined.PaymentCycle);
                jsonObject.Add("creditLimitInCents", productDefined.CreditLimitInCents);
                jsonObject.Add("maxBalanceTransferAllowedInCents", productDefined.MaxBalanceTransferAllowedInCents);
                jsonObject.Add("reward", productDefined.Reward);
                jsonObject.Add("cardBackgroundHex", productDefined.CardBackgroundHex);
                break;
        }

        return jsonObject.ToJsonString();
    }

    private static string FormatDateTime(DateTime dateTime)
    {
        // Format to match Java's "yyyy-MM-dd HH:mm:ss.SSSSSS z" pattern
        return dateTime.ToUniversalTime().ToString("yyyy-MM-dd HH:mm:ss.ffffff UTC");
    }
}
