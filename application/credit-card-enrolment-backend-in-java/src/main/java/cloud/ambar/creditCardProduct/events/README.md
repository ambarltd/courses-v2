Events represent facts about state changes that have occurred in the system. Instead of storing the current state of an 
entity directly, the system stores a series of events that describe each change over time. The current state is derived 
by replaying these events in the order they occurred.

Events are immutable, meaning once they are created and stored, they cannot be modified. They are a record of what happened 
in the system, not what actions were requested (as with commands).

An event typically contains:

* Event Type: A description of the specific action that occurred (e.g., OrderPlaced, AccountDebited, ItemUpdated).
* Aggregate Identifier: The unique ID of the entity or aggregate the event applies to.
* Timestamp: The exact time when the event occurred.
* Payload: Data describing the state change, such as updated fields, amounts, or new values for the entity.
* Metadata (optional): Information such as the user who triggered the event, the source system, or tracking IDs.

Events are used to:

* Rebuild the current state of an aggregate by replaying the series of events.
* Trigger reactions or side effects in other parts of the system, such as sending notifications or updating read models (in CQRS).
* Provide an audit trail, capturing the full history of changes in the system for accountability and debugging.

By relying on events as the source of truth, event sourcing allows for greater traceability, flexibility in replaying or 
restoring state, and the ability to respond to changes in a distributed, asynchronous manner.