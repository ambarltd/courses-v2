export interface SerializedEvent {
    id?: number;
    event_id: string;
    aggregate_id: string;
    causation_id: string;
    correlation_id: string;
    aggregate_version: number;
    json_payload: string;
    json_metadata: string;
    recorded_on: string;
    event_name: string;
}