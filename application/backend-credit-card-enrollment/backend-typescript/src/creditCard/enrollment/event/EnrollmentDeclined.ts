import { TransformationEvent } from '../../../common/event/TransformationEvent';
import { Enrollment } from '../aggregate/Enrollment';
import { EnrollmentStatus } from '../aggregate/EnrollmentStatus';

export class EnrollmentDeclined extends TransformationEvent<Enrollment> {
    constructor(
        eventId: string,
        aggregateId: string,
        aggregateVersion: number,
        correlationId: string,
        causationId: string,
        recordedOn: Date,
        public readonly reasonCode: string,
        public readonly reasonDescription: string
    ) {
        super(eventId, aggregateId, aggregateVersion, correlationId, causationId, recordedOn);
    }

    transformAggregate(aggregate: Enrollment): Enrollment {
        return new Enrollment(
            this.aggregateId,
            this.aggregateVersion,
            aggregate.userId,
            aggregate.productId,
            EnrollmentStatus.Declined,
            aggregate.annualIncomeInCents,
            aggregate.enrollmentFirstRequestedOn
        );
    }
}