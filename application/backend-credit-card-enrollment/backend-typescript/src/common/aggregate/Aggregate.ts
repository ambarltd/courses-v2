export abstract class Aggregate {
    protected constructor(
        public readonly aggregateId: string,
        public readonly aggregateVersion: number
    ) {}
}