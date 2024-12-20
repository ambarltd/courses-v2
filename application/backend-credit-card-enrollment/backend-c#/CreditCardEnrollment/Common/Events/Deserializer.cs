using System.Text.Json;
using System.Text.Json.Nodes;
using CreditCardEnrollment.Domain.Enrollment.Events;
using CreditCardEnrollment.Domain.Product.Events;

namespace CreditCardEnrollment.Common.Events;

public interface IDeserializer
{
    Event Deserialize(SerializedEvent serializedEvent);
}

public class Deserializer : IDeserializer
{
    public Event Deserialize(SerializedEvent serializedEvent)
    {
        return serializedEvent.EventName switch
        {
            "CreditCard_Enrollment_EnrollmentRequested" => new EnrollmentRequested
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn),
                AnnualIncomeInCents = PayloadInt(serializedEvent.JsonPayload, "annualIncomeInCents"),
                ProductId = PayloadString(serializedEvent.JsonPayload, "productId"),
                UserId = PayloadString(serializedEvent.JsonPayload, "userId")
            },
            "CreditCard_Enrollment_EnrollmentAccepted" => new EnrollmentAccepted
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn),
                ReasonDescription = PayloadString(serializedEvent.JsonPayload, "reasonDescription"),
                UserId = PayloadString(serializedEvent.JsonPayload, "userId"),
                ProductId = PayloadString(serializedEvent.JsonPayload, "productId")
            },
            "CreditCard_Enrollment_EnrollmentDeclined" => new EnrollmentDeclined
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn),
                Reason = PayloadString(serializedEvent.JsonPayload, "reasonDescription"),
                UserId = PayloadString(serializedEvent.JsonPayload, "userId"),
                ProductId = PayloadString(serializedEvent.JsonPayload, "productId")
            },
            "CreditCard_Product_ProductActivated" => new ProductActivated
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn)
            },
            "CreditCard_Product_ProductDeactivated" => new ProductDeactivated
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn)
            },
            "CreditCard_Product_ProductDefined" => new ProductDefined
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn),
                Name = PayloadString(serializedEvent.JsonPayload, "name"),
                InterestInBasisPoints = PayloadInt(serializedEvent.JsonPayload, "interestInBasisPoints"),
                AnnualFeeInCents = PayloadInt(serializedEvent.JsonPayload, "annualFeeInCents"),
                PaymentCycle = PayloadString(serializedEvent.JsonPayload, "paymentCycle"),
                CreditLimitInCents = PayloadInt(serializedEvent.JsonPayload, "creditLimitInCents"),
                MaxBalanceTransferAllowedInCents = PayloadInt(serializedEvent.JsonPayload, "maxBalanceTransferAllowedInCents"),
                Reward = PayloadString(serializedEvent.JsonPayload, "reward"),
                CardBackgroundHex = PayloadString(serializedEvent.JsonPayload, "cardBackgroundHex")
            },
            _ => throw new ArgumentException($"Unknown event type: {serializedEvent.EventName}")
        };
    }

    private static DateTime ToDateTime(string? recordedOn)
    {
        if (string.IsNullOrEmpty(recordedOn))
            return DateTime.UtcNow;

        // Parse the date format "yyyy-MM-dd HH:mm:ss.SSSSSS z"
        if (DateTime.TryParse(recordedOn, out DateTime result))
            return result;

        throw new ArgumentException($"Invalid date format: {recordedOn}");
    }

    private static string PayloadString(string jsonString, string fieldName)
    {
        try
        {
            var jsonNode = JsonNode.Parse(jsonString);
            return jsonNode?[fieldName]?.GetValue<string>() ?? string.Empty;
        }
        catch (Exception ex)
        {
            throw new ArgumentException($"Error parsing JSON field {fieldName}", ex);
        }
    }

    private static int PayloadInt(string jsonString, string fieldName)
    {
        try
        {
            var jsonNode = JsonNode.Parse(jsonString);
            return jsonNode?[fieldName]?.GetValue<int>() ?? 0;
        }
        catch (Exception ex)
        {
            throw new ArgumentException($"Error parsing JSON field {fieldName}", ex);
        }
    }
}
