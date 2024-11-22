See more background information about events in the common/event directory.

This directory contains event payload shapes for adding additional information to events. The classes here have a name for
the event type they represent, and then remaining fields are meant to be serialized into json and stored in the payload
attribute of an event when written to the event store. This allows for all events to have the necessary attributes for 
event sourcing, while giving us a flexible payload field to pass additional information with the event.