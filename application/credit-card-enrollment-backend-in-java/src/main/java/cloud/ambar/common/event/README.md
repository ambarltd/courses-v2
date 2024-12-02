Events represent state changes that have occurred in the system. 
Instead of storing state, our system stores a series of events.
Current state is derived by replaying these events in the order they occurred.

Events are immutable, meaning once they are created and stored, they cannot be modified. 
They are a record of what happened in the system.

An event typically contains:

* Event Name: A description of the specific action that occurred (e.g., OrderPlaced, AccountDebited, UserSignedUp).
* Aggregate Identifier: The unique ID of the aggregate the event belongs in.
* Timestamp: The time when the Event occurred.
* Payload: Data describing the state change (the properties of the aggregate that have been changed).
* Metadata (optional): Information such as the user agent or IP of the end user.

Events are used to:

* Rebuild the current state of an aggregate by replaying the series of events.
* Trigger reactions or side effects in other parts of the system, such as sending notifications or updating read models.
* Provide an audit trail, capturing the full history of changes in the system for accountability and debugging.

By relying on events as the source of truth, serializedEvent sourcing allows for greater traceability, flexibility in replaying or 
restoring state, and the ability to respond to changes in a distributed, asynchronous manner.

This directory contains our base definition for an serializedEvent which can model all events in our system. We capture the key 
details needed for any serializedEvent to be able to be stored. Unique information relating to the purpose of a specific serializedEvent can
then be put into the payload as free form json, giving us highly flexible events while still containing all the necessary
information.