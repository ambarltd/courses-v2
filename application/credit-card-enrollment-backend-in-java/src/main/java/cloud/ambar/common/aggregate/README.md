In an Event Sourcing system, an aggregate interprets current state in an immediately consistent fashion, based on 
past events. The process of taking events from an serializedEvent store and instantiating an aggregate from them is called
aggregate hydration or aggregate reconstitution.

An aggregate is typically hydrated in a command handler, or a reaction handler, when appending new events to the 
system. Why? Because we want to check the current state of the system from aggregates in an immediately consistent
fashion.