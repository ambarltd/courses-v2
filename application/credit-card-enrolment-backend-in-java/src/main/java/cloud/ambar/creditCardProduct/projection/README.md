In an event sourcing system, projections and reactions are ways of deriving useful data from stored events.

### Projections
A projection transforms the sequence of domain events into a queryable state, often used to build read models. Projections 
take the immutable stream of events and create a representation of the current state of an entity, which is then stored 
in a projection database optimized for queries. This allows the read side of a system (e.g., for CQRS) to be decoupled 
from the write side.

When an event is emitted (e.g., OrderPlaced, ProductUpdated), a projection listens to the stream of events and filters 
the relevant events it needs to process. For example, a projection that builds a list of user orders would only listen 
for OrderPlaced and OrderCanceled events. It updates the read model by applying the event data, ensuring the model 
reflects the latest state.

### Reactions
A reaction (or event handler) performs a side effect in response to an event. While projections update state, reactions 
may trigger actions like sending notifications, updating external systems, or initiating new workflows. Reactions also 
filter relevant events, allowing for targeted responses to specific state changes.

#### Filtering of Events
Both projections and reactions typically filter the events they process based on:

* Event type: Only processing events relevant to their specific purpose (e.g., an inventory projection may only care 
about ItemAdded and ItemRemoved events).
* Aggregate ID: Sometimes, projections or reactions may be scoped to events from specific entities or aggregates.

#### Writing to a Projection Database
The projection database stores the read model state, which is optimized for fast querying. For example, while the event 
store captures all historical changes, a projection for user profiles may store the latest user information in a separate 
table or NoSQL database. This ensures queries are efficient and the read side is decoupled from the complexity of event 
processing.

The system continuously updates the projection database as new events arrive, keeping the read model in sync with the 
most recent state changes. This enables high-performance queries and ensures that the read side remains highly available 
and scalable.