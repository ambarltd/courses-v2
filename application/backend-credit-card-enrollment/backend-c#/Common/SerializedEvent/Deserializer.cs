using System.Text.Json;
using System.Text.Json.Nodes;
using CreditCardEnrollment.CreditCard.Enrollment.Event;
using CreditCardEnrollment.CreditCard.Product.Event;

namespace CreditCardEnrollment.Common.SerializedEvent;

public class Deserializer
{
    public Event.Event Deserialize(SerializedEvent serializedEvent)
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
                ReasonCode = PayloadString(serializedEvent.JsonPayload, "reasonCode"),
                ReasonDescription = PayloadString(serializedEvent.JsonPayload, "reasonDescription"),
            },
            "CreditCard_Enrollment_EnrollmentDeclined" => new EnrollmentDeclined
            {
                EventId = serializedEvent.EventId,
                AggregateId = serializedEvent.AggregateId,
                AggregateVersion = serializedEvent.AggregateVersion,
                CorrelationId = serializedEvent.CorrelationId,
                CausationId = serializedEvent.CausationId,
                RecordedOn = ToDateTime(serializedEvent.RecordedOn),
                ReasonCode = PayloadString(serializedEvent.JsonPayload, "reasonCode"),
                ReasonDescription = PayloadString(serializedEvent.JsonPayload, "reasonDescription"),
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

    private static DateTime ToDateTime(string recordedOn)
    {
        if (!recordedOn.EndsWith(" UTC"))
        {
            throw new ArgumentException($"Invalid date format: {recordedOn}");
        }
        
        if (DateTime.TryParseExact(recordedOn[..^4], 
                "yyyy-MM-dd HH:mm:ss.ffffff",
                System.Globalization.CultureInfo.InvariantCulture,
                System.Globalization.DateTimeStyles.AssumeUniversal,
                out var result))
        {
            return DateTime.SpecifyKind(result, DateTimeKind.Utc);
        }

        throw new ArgumentException($"Invalid date format: {recordedOn}");
    }

    private static string PayloadString(string jsonString, string fieldName)
    {
        var jsonNode = JsonNode.Parse(jsonString);
        var value = jsonNode?[fieldName]?.GetValue<string>();
        if (value == null)
            throw new ArgumentException($"Required field {fieldName} is null or missing");
        return value;
    }
    
    private static int PayloadInt(string jsonString, string fieldName)
    {
        var jsonNode = JsonNode.Parse(jsonString);
        if (jsonNode?[fieldName] == null)
            throw new ArgumentException($"Required field {fieldName} is null or missing");
        return jsonNode[fieldName]!.GetValue<int>();
    }
}
