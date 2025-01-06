# Reaction

A reaction performs a side effect in response to an Event. While projections update state, reactions 
may trigger actions like sending notifications, updating external systems, or initiating new workflows. Reactions also 
filter relevant events, allowing for targeted responses to specific state changes. Reactions are not only responsible
for performing the side effect, but also for ensuring that the side effect is idempotent by writing the result of the
side effect into the Event Store as an Event.

## How Reactions Work

Reactions are built by listening to events, triggering side effects, and recording the result of those side effects
to the Event Store. This behavior is captured by extending a `ReactionHandler`.

### How do events get sent from the Event Store to the Reaction Handlers?

We use Ambar to read events from the Event Store and send them to the Reaction Handlers, via an HTTP endpoint.
The HTTP endpoint is defined through extending a `ReactionController`, which will receive the events and send
them to the corresponding `ReactionHandler`.

#### How do we make sure that events are sent at least once, and in order per aggregate, to a Reaction Handler?

Ambar takes care of this out of the box. All you have to take care of is making every `ReactionController` idempotent.
To make sure reaction endpoint only process Events once, the `ReactionHandler` has to commit the results of its
side effect into the Event Store with a new Event. This way, if the reaction is triggered again, it will be able to find
an existing event in the Event Store. Note that the Reaction Event has to have a deterministic event id, so that
we can check if the event has already been processed with the `ReactionHandler`.

### Where can I find the Ambar configuration?

The Ambar configuration is located in the `local-development/ambar-config.yml`.

### In ambar-config.yml, why are events ordered per correlation id, instead of aggregate id?

Ordering events per correlation id retains the order of events per aggregate, but also retains the order of events
across related aggregates. E.g., if you have an aggregate for November, and an aggregate for December, and the aggregate
for December directly follows the aggregate for November (using the same correlation id), Ambar will give you the events
in order per aggregate, but will also retain order across aggregates (Ambar will send November first, and December
second, so you can react in order).

