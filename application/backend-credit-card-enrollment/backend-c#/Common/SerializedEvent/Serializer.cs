using System.Text.Json.Nodes;
using CreditCardEnrollment.CreditCard.Enrollment.Event;

namespace CreditCardEnrollment.Common.SerializedEvent;

public class Serializer
{
    public SerializedEvent Serialize(Event.Event @event)
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

    private static string DetermineEventName(Event.Event @event)
    {
        return @event switch
        {
            EnrollmentRequested => "CreditCard_Enrollment_EnrollmentRequested",
            EnrollmentAccepted => "CreditCard_Enrollment_EnrollmentAccepted",
            EnrollmentDeclined => "CreditCard_Enrollment_EnrollmentDeclined",
            _ => throw new ArgumentException($"Unknown event type: {@event.GetType().Name}")
        };
    }

    private static string CreateJsonPayload(Event.Event @event)
    {
        var jsonObject = new JsonObject();

        switch (@event)
        {
            case EnrollmentRequested enrollmentRequested:
                jsonObject.Add("userId", enrollmentRequested.UserId);
                jsonObject.Add("productId", enrollmentRequested.ProductId);
                jsonObject.Add("annualIncomeInCents", enrollmentRequested.AnnualIncomeInCents);
                break;

            case EnrollmentAccepted enrollmentAccepted:
                jsonObject.Add("reasonCode", enrollmentAccepted.ReasonCode);
                jsonObject.Add("reasonDescription", enrollmentAccepted.ReasonDescription);
                break;

            case EnrollmentDeclined enrollmentDeclined:
                jsonObject.Add("reasonCode", enrollmentDeclined.ReasonCode);
                jsonObject.Add("reasonDescription", enrollmentDeclined.ReasonDescription);
                break;
        }

        return jsonObject.ToJsonString();
    }

    private static string FormatDateTime(DateTime dateTime)
    {
        return dateTime.ToUniversalTime().ToString("yyyy-MM-dd HH:mm:ss.ffffff UTC");
    }
}
