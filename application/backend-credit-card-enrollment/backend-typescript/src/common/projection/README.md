# Projection

A projection is a read model that is derived from the events in the system. Projections are used to query the current
state of the system. For example, in an ecommerce website users will need to know which items are available,
before they add an item to their cart. This allows the read side of a system to often be decoupled
from the write side.

When an Event is emitted (e.g., OrderPlaced, ProductUpdated), a projection listens to the stream of events and filters
the relevant events it needs to process. For example, a projection that builds a list of user orders would only listen
for OrderPlaced and OrderCanceled events. It updates the read model by applying the Event data, ensuring the model
reflects the latest state. 

Projections continuously update the projection database as new events arrive, keeping the read model in sync with the
most recent state changes. This enables high-performance queries and ensures that the read side remains highly available
and scalable.

You can use projections for sharing state with your end users, but also to do validation in command handlers. But 
note that projections are built asynchronously, so they are eventually consistent. If you need to enforce business 
rules in an immediately consistent manner, you should do so by loading aggregates as opposed to reading projections.

## How Projections Work

Projections are built by listening to events and updating the read model accordingly. When an event is received,
we update a projection database (MongoDB), based on the contents of the event and any existing data in the 
projection database. This behavior is captured by extending a `ProjectionHandler`.

### How do events get sent from the Event Store to the Projection Handlers?

We use Ambar to read events from the Event Store and send them to the Projection Handlers, via an HTTP endpoint. 
The HTTP endpoint is defined through extending a `ProjectionController`, which will receive the events and send 
them to the corresponding `ProjectionHandler`.

#### How do we make sure that events are sent at least once, and in order per aggregate, to a Projection Handler?

Ambar takes care of this out of the box. All you have to take care of is making every `ProjectionController` idempotent.
To make sure projections endpoint only process events once, the `ProjectionController` uses an abstraction called
`ProjectedEvent` which keeps track of every event that has already been processed.

### Where can I find the Ambar configuration?

The Ambar configuration is located in the `local-development/ambar-config.yml`.

### In ambar-config.yml, why are events ordered per correlation id, instead of aggregate id?

Ordering events per correlation id retains the order of events per aggregate, but also retains the order of events
across related aggregates. E.g., if you have an aggregate for November, and an aggregate for December, and the aggregate
for December directly follows the aggregate for November (using the same correlation id), Ambar will give you the events
in order per aggregate, but will also retain order across aggregates (Ambar will project November first, and December 
second).