In a CQRS (Command Query Responsibility Segregation) system, commands represent requests to perform an action that modifies 
the state of the system. They are typically initiated by users or other system components to trigger state-changing 
operations like creating, updating, or deleting an entity.

A command in CQRS is an intent to change state, and it must contain enough information to execute that change. 
This often includes:

* Command Type: The specific action to be performed (e.g., CreateOrder, UpdateAccount, CancelReservation). In our case, the
class itself can be used to identify the type of command.
* Target Identifier: A unique identifier, such as an aggregate ID, that specifies which entity or aggregate the command affects.
* Relevant Data: The necessary attributes to execute the action (e.g., new item details for an update, a customer’s ID, 
or a quantity for an inventory adjustment).
* Metadata (optional): Additional information such as the timestamp of when the command was issued or the user who initiated it.

Commands are imperative—they signal that something must happen in the system. After receiving a command, the system 
validates it against business rules and then executes the corresponding logic, often resulting in new events that record 
the state change in an event store.