import { Aggregate } from '../aggregate/Aggregate';

export interface AggregateAndEventIdsInLastEvent<T extends Aggregate> {
    aggregate: T;
    eventIdOfLastEvent: string;
    correlationIdOfLastEvent: string;
}