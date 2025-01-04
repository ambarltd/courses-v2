import { SerializedEvent } from '../serializedEvent/SerializedEvent';

export interface AmbarHttpRequest {
    data_source_id: string;
    data_source_description: string;
    data_destination_id: string;
    data_destination_description: string;
    payload: SerializedEvent;
}