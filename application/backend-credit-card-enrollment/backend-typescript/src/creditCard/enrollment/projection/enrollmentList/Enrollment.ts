export class Enrollment {
    constructor(
        // needs to be _id to be recognized as an _id field by MongoDB
        public readonly _id: string,
        public readonly userId: string,
        public readonly productId: string,
        public readonly requestedDate: Date,
        public status: string,
        public statusReason?: string,
        public reviewedOn?: Date
    ) {}
}