import { Event } from '../event/Event';
import { SerializedEvent } from './SerializedEvent';
import { EnrollmentRequested } from "../../creditCard/enrollment/event/EnrollmentRequested";
import { EnrollmentAccepted } from "../../creditCard/enrollment/event/EnrollmentAccepted";
import { EnrollmentDeclined } from "../../creditCard/enrollment/event/EnrollmentDeclined";
import { ProductActivated } from "../../creditCard/product/event/ProductActivated";
import { ProductDeactivated } from "../../creditCard/product/event/ProductDeactivated";
import { ProductDefined } from "../../creditCard/product/event/ProductDefined";
import { injectable } from "tsyringe";

@injectable()
export class Deserializer {
    deserialize(serializedEvent: SerializedEvent): Event {
        const recordedOn = this.parseDateTime(serializedEvent.recorded_on);
        const payload = JSON.parse(serializedEvent.json_payload);

        switch (serializedEvent.event_name) {
            case 'CreditCard_Enrollment_EnrollmentRequested':
                return new EnrollmentRequested(
                    this.parseString(serializedEvent.event_id),
                    this.parseString(serializedEvent.aggregate_id),
                    this.parseNumber(serializedEvent.aggregate_version),
                    this.parseString(serializedEvent.correlation_id),
                    this.parseString(serializedEvent.causation_id),
                    recordedOn,
                    this.parseString(payload.userId),
                    this.parseString(payload.productId),
                    this.parseNumber(payload.annualIncomeInCents)
                );

            case 'CreditCard_Enrollment_EnrollmentAccepted':
                return new EnrollmentAccepted(
                    this.parseString(serializedEvent.event_id),
                    this.parseString(serializedEvent.aggregate_id),
                    this.parseNumber(serializedEvent.aggregate_version),
                    this.parseString(serializedEvent.correlation_id),
                    this.parseString(serializedEvent.causation_id),
                    recordedOn,
                    this.parseString(payload.reasonCode),
                    this.parseString(payload.reasonDescription)
                );

            case 'CreditCard_Enrollment_EnrollmentDeclined':
                return new EnrollmentDeclined(
                    this.parseString(serializedEvent.event_id),
                    this.parseString(serializedEvent.aggregate_id),
                    this.parseNumber(serializedEvent.aggregate_version),
                    this.parseString(serializedEvent.correlation_id),
                    this.parseString(serializedEvent.causation_id),
                    recordedOn,
                    this.parseString(payload.reasonCode),
                    this.parseString(payload.reasonDescription)
                );

            case 'CreditCard_Product_ProductActivated':
                return new ProductActivated(
                    this.parseString(serializedEvent.event_id),
                    this.parseString(serializedEvent.aggregate_id),
                    this.parseNumber(serializedEvent.aggregate_version),
                    this.parseString(serializedEvent.correlation_id),
                    this.parseString(serializedEvent.causation_id),
                    recordedOn
                );

            case 'CreditCard_Product_ProductDeactivated':
                return new ProductDeactivated(
                    this.parseString(serializedEvent.event_id),
                    this.parseString(serializedEvent.aggregate_id),
                    this.parseNumber(serializedEvent.aggregate_version),
                    this.parseString(serializedEvent.correlation_id),
                    this.parseString(serializedEvent.causation_id),
                    recordedOn
                );

            case 'CreditCard_Product_ProductDefined':
                return new ProductDefined(
                    this.parseString(serializedEvent.event_id),
                    this.parseString(serializedEvent.aggregate_id),
                    this.parseNumber(serializedEvent.aggregate_version),
                    this.parseString(serializedEvent.correlation_id),
                    this.parseString(serializedEvent.causation_id),
                    recordedOn,
                    this.parseString(payload.name),
                    this.parseNumber(payload.interestInBasisPoints),
                    this.parseNumber(payload.annualFeeInCents),
                    this.parseString(payload.paymentCycle),
                    this.parseNumber(payload.creditLimitInCents),
                    this.parseNumber(payload.maxBalanceTransferAllowedInCents),
                    this.parseString(payload.reward),
                    this.parseString(payload.cardBackgroundHex)
                );

            default:
                throw new Error(`Unknown event type: ${serializedEvent.event_name}`);
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

    private parseString(value: any): string {
        if (typeof value !== 'string') {
            throw new Error(`Expected string but got ${typeof value}`);
        }
        return value;
    }

    private parseNumber(value: any): number {
        const parsed = Number(value);
        if (isNaN(parsed)) {
            throw new Error(`Expected number but got ${typeof value}`);
        }
        return parsed;
    }
}