# Event

## What are Events?

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

## Why use Events?

Events are used to:

* Rebuild the current state of an aggregate by replaying the series of events.
* Trigger side effects (reactions) such as sending notifications.
* Asynchronously update read models (projections).
* Provide an audit trail, capturing the full history of changes in the system for compliance and debugging.

By relying on events as the source of truth, Event Sourcing allows for greater traceability, flexibility in replaying or 
restoring state, and the ability to respond to changes in a distributed, asynchronous manner.

## Abstractions 

This directory contains our base definition for an Event. That is, `event_id`, `aggregate_id`, `aggregate_version`, 
`causation_id`, `correlation_id`, `recorded_on`. The event_name column, which is the name of the event, is not included
because it's based on a mapping of the event class name to the event name. The `payload` column and `metadata` column
are also not included because they are based on the event class properties. We use an abstraction called
Serialized Event (see `src/main/java/cloud/ambar/common/serializedevent/SerializedEvent.java`) to store the `event_name`,
`payload`, and `metadata`. 

**Why are there two extra abstract classes for creation events and transformation events?**

Creation events are events that are used to create an aggregate. They are used to create the initial state of an aggregate.
Transformation events are events that are used to transform an aggregate. They are used to change the state of an aggregate.

When reconstituting / hydrating an aggregate, it's better not to have a default state of the aggregate which contains invalid
state. Instead, it's better to codify into our type system which events can create a valid aggregate state on their own 
and which events can transform an aggregate. This way, we can ensure that the aggregate is always in a valid state.