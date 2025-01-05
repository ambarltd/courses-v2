import { Event } from '../event/Event';
import { SerializedEvent } from './SerializedEvent';
import {EnrollmentRequested} from "../../creditCard/enrollment/event/EnrollmentRequested";
import {EnrollmentAccepted} from "../../creditCard/enrollment/event/EnrollmentAccepted";
import {EnrollmentDeclined} from "../../creditCard/enrollment/event/EnrollmentDeclined";
import {injectable} from "tsyringe";

@injectable()
export class Serializer {
    serialize(event: Event): SerializedEvent {
        return {
            event_id: event.eventId,
            aggregate_id: event.aggregateId,
            aggregate_version: event.aggregateVersion,
            correlation_id: event.correlationId,
            causation_id: event.causationId,
            recorded_on: this.formatDateTime(event.recordedOn),
            event_name: this.determineEventName(event),
            json_payload: this.createJsonPayload(event),
            json_metadata: '{}'
        };
    }

    private determineEventName(event: Event): string {
        if (event instanceof EnrollmentRequested) {
            return 'CreditCard_Enrollment_EnrollmentRequested';
        }
        if (event instanceof EnrollmentAccepted) {
            return 'CreditCard_Enrollment_EnrollmentAccepted';
        }
        if (event instanceof EnrollmentDeclined) {
            return 'CreditCard_Enrollment_EnrollmentDeclined';
        }
        throw new Error(`Unknown event type: ${event.constructor.name}`);
    }

    private createJsonPayload(event: Event): string {
        const payload: Record<string, any> = {};

        if (event instanceof EnrollmentRequested) {
            payload.userId = event.userId;
            payload.productId = event.productId;
            payload.annualIncomeInCents = event.annualIncomeInCents;
        } else if (event instanceof EnrollmentAccepted) {
            payload.reasonCode = event.reasonCode;
            payload.reasonDescription = event.reasonDescription;
        } else if (event instanceof EnrollmentDeclined) {
            payload.reasonCode = event.reasonCode;
            payload.reasonDescription = event.reasonDescription;
        }

        return JSON.stringify(payload);
    }

    private formatDateTime(date: Date): string {
        return date.toISOString().replace('Z', ' UTC');
    }
}