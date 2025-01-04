import { Event } from '../event/Event';

export abstract class ProjectionHandler {
    public abstract project(event: Event): Promise<void>;
}