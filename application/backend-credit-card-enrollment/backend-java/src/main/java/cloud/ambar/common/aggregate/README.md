# Aggregate

In Event Sourcing system, the Aggregate is an in-memory representation of the current state  of the system based on 
past events. The process of taking events from the Event Store and instantiating an Aggregate from them is called
Aggregate hydration or Aggregate reconstitution. 

An Aggregate is typically hydrated in a command handler, or a reaction handler, when appending new events to the 
system. Why? Because we want to check the current state of the system from Aggregates in an immediately consistent
fashion. The Aggregate should be implemented in an immediately consistent fashion through the use of optimistic 
or pessimistic locking when reconstituting the Aggregate.