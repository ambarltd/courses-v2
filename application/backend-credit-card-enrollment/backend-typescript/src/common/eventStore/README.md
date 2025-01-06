# Event Store

The Event Store is responsible for saving new Events and fetching existing Events to hydrate / reconstitute 
Aggregates. 

The Event Store saves Events, but it does not save them directly, it first converts them to a SerializedEvent.
The SerializedEvent is a representation of the Event that can be stored in a database.

Additionally, the Event Store does not simply return aggregates, but it returns an Aggregate plus 
Event Ids, that would be necessary to append more events to the Aggregate (event_id and correlation_id in the
last event of that Aggregate).