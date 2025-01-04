import { Event } from '../event/Event';
import { SerializedEvent } from './SerializedEvent';
import {EnrollmentRequested} from "../../creditCard/enrollment/event/EnrollmentRequested";
import {EnrollmentAccepted} from "../../creditCard/enrollment/event/EnrollmentAccepted";
import {EnrollmentDeclined} from "../../creditCard/enrollment/event/EnrollmentDeclined";
import {ProductActivated} from "../../creditCard/product/event/ProductActivated";
import {ProductDeactivated} from "../../creditCard/product/event/ProductDeactivated";
import {ProductDefined} from "../../creditCard/product/event/ProductDefined";


export class Deserializer {
    deserialize(serializedEvent: SerializedEvent): Event {
        const recordedOn = this.parseDateTime(serializedEvent.recordedOn);
        const payload = JSON.parse(serializedEvent.jsonPayload);

        switch (serializedEvent.eventName) {
            case 'CreditCard_Enrollment_EnrollmentRequested':
                return new EnrollmentRequested(
                    serializedEvent.eventId,
                    serializedEvent.aggregateId,
                    serializedEvent.aggregateVersion,
                    serializedEvent.correlationId,
                    serializedEvent.causationId,
                    recordedOn,
                    payload.userId,
                    payload.productId,
                    payload.annualIncomeInCents
                );

            case 'CreditCard_Enrollment_EnrollmentAccepted':
                return new EnrollmentAccepted(
                    serializedEvent.eventId,
                    serializedEvent.aggregateId,
                    serializedEvent.aggregateVersion,
                    serializedEvent.correlationId,
                    serializedEvent.causationId,
                    recordedOn,
                    payload.reasonCode,
                    payload.reasonDescription
                );

            case 'CreditCard_Enrollment_EnrollmentDeclined':
                return new EnrollmentDeclined(
                    serializedEvent.eventId,
                    serializedEvent.aggregateId,
                    serializedEvent.aggregateVersion,
                    serializedEvent.correlationId,
                    serializedEvent.causationId,
                    recordedOn,
                    payload.reasonCode,
                    payload.reasonDescription
                );

            case 'CreditCard_Product_ProductActivated':
                return new ProductActivated(
                    serializedEvent.eventId,
                    serializedEvent.aggregateId,
                    serializedEvent.aggregateVersion,
                    serializedEvent.correlationId,
                    serializedEvent.causationId,
                    recordedOn
                );

            case 'CreditCard_Product_ProductDeactivated':
                return new ProductDeactivated(
                    serializedEvent.eventId,
                    serializedEvent.aggregateId,
                    serializedEvent.aggregateVersion,
                    serializedEvent.correlationId,
                    serializedEvent.causationId,
                    recordedOn
                );

            case 'CreditCard_Product_ProductDefined':
                return new ProductDefined(
                    serializedEvent.eventId,
                    serializedEvent.aggregateId,
                    serializedEvent.aggregateVersion,
                    serializedEvent.correlationId,
                    serializedEvent.causationId,
                    recordedOn,
                    payload.name,
                    payload.interestInBasisPoints,
                    payload.annualFeeInCents,
                    payload.paymentCycle,
                    payload.creditLimitInCents,
                    payload.maxBalanceTransferAllowedInCents,
                    payload.reward,
                    payload.cardBackgroundHex
                );

            default:
                throw new Error(`Unknown event type: ${serializedEvent.eventName}`);
        }
    }

    private parseDateTime(dateStr: string): Date {
        if (!dateStr.endsWith(' UTC')) {
            throw new Error(`Invalid date format: ${dateStr}`);
        }
        const parsed = new Date(dateStr.slice(0, -4) + 'Z');
        if (isNaN(parsed.getTime())) {
            throw new Error(`Invalid date format: ${dateStr}`);
        }
        return parsed;
    }
}