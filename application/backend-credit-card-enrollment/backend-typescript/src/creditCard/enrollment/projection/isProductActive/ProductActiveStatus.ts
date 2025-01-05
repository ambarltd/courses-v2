export class ProductActiveStatus {
    constructor(
        // needs to be _id to be recognized as an _id field by MongoDB
        public readonly _id: string,
        public active: boolean
    ) {}
}