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
            eventId: event.eventId,
            aggregateId: event.aggregateId,
            aggregateVersion: event.aggregateVersion,
            correlationId: event.correlationId,
            causationId: event.causationId,
            recordedOn: this.formatDateTime(event.recordedOn),
            eventName: this.determineEventName(event),
            jsonPayload: this.createJsonPayload(event),
            jsonMetadata: '{}'
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