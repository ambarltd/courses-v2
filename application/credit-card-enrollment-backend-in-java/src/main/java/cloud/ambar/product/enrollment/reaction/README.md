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