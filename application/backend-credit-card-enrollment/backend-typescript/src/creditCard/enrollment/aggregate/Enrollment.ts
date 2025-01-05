import { Aggregate } from '../../../common/aggregate/Aggregate';
import { EnrollmentStatus } from './EnrollmentStatus';

export class Enrollment extends Aggregate {
    constructor(
        aggregateId: string,
        aggregateVersion: number,
        public readonly userId: string,
        public readonly productId: string,
        public readonly status: EnrollmentStatus,
        public readonly annualIncomeInCents: number,
        public readonly enrollmentFirstRequestedOn: Date
    ) {
        super(aggregateId, aggregateVersion);
    }
}