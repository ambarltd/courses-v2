import { CreationEvent } from '../../../common/event/CreationEvent';
import { Enrollment } from '../aggregate/Enrollment';
import { EnrollmentStatus } from '../aggregate/EnrollmentStatus';

export class EnrollmentRequested extends CreationEvent<Enrollment> {
    constructor(
        eventId: string,
        aggregateId: string,
        aggregateVersion: number,
        correlationId: string,
        causationId: string,
        recordedOn: Date,
        public readonly userId: string,
        public readonly productId: string,
        public readonly annualIncomeInCents: number
    ) {
        super(eventId, aggregateId, aggregateVersion, correlationId, causationId, recordedOn);
    }

    createAggregate(): Enrollment {
        return new Enrollment(
            this.aggregateId,
            this.aggregateVersion,
            this.userId,
            this.productId,
            EnrollmentStatus.Requested,
            this.annualIncomeInCents,
            this.recordedOn
        );
    }
}