export class EnrollmentListItem {
    constructor(
        // needs to be _id to be recognized as an _id field by MongoDB
        public readonly _id: string,
        public readonly userId: string,
        public readonly productId: string,
        public readonly productName: string,
        public readonly requestedDate: Date,
        public readonly status: string,
        public readonly statusReason?: string,
        public readonly reviewedOn?: Date
    ) {}
}