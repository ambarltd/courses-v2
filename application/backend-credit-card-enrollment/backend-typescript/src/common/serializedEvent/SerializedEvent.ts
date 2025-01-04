export interface SerializedEvent {
    id?: number;
    eventId: string;
    aggregateId: string;
    causationId: string;
    correlationId: string;
    aggregateVersion: number;
    jsonPayload: string;
    jsonMetadata: string;
    recordedOn: string;
    eventName: string;
}