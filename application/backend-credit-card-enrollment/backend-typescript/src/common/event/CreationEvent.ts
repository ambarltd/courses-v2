import { Event } from './Event';
import { Aggregate } from '../aggregate/Aggregate';

export abstract class CreationEvent<T extends Aggregate> extends Event {
    abstract createAggregate(): T;
}