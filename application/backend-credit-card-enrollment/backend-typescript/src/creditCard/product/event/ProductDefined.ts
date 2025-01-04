import { Event } from '../../../common/event/Event';

export class ProductDefined extends Event {
    constructor(
        eventId: string,
        aggregateId: string,
        aggregateVersion: number,
        correlationId: string,
        causationId: string,
        recordedOn: Date,
        public readonly name: string,
        public readonly interestInBasisPoints: number,
        public readonly annualFeeInCents: number,
        public readonly paymentCycle: string,
        public readonly creditLimitInCents: number,
        public readonly maxBalanceTransferAllowedInCents: number,
        public readonly reward: string,
        public readonly cardBackgroundHex: string
    ) {
        super(eventId, aggregateId, aggregateVersion, correlationId, causationId, recordedOn);
    }
}
