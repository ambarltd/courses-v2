export abstract class Event {
    constructor(
        public readonly eventId: string,
        public readonly aggregateId: string,
        public readonly aggregateVersion: number,
        public readonly correlationId: string,
        public readonly causationId: string,
        public readonly recordedOn: Date
    ) {}
}