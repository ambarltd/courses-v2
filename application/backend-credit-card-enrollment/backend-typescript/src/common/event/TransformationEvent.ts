import { Event } from './Event';
import { Aggregate } from '../aggregate/Aggregate';

export abstract class TransformationEvent<T extends Aggregate> extends Event {
    abstract transformAggregate(aggregate: T): T;
}