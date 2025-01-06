# Command Handler

A Command Handler in an EventSourcing system is responsible for taking statements of intent (commands) from end users
or other systems (both internal and external), performing validation, and upon valid conditions, adding new Events
to the Event Store.

To do this, the Command Handler reads past events from the Event store to hydrate / reconstitute an Aggregate. 
Once the aggregate is hydrated, the Command Handler checks for any business rules or constraints (e.g., ensuring an 
order hasn’t already been completed or that an account has sufficient balance).

If all validations succeed, the Command Handler generates a new Event reflecting the state change requested by the command. 
This Event is then written back to the Event store, allowing the system to evolve while maintaining a full history of 
all changes.
