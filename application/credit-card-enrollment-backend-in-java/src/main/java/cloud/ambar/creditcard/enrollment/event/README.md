See more background information about events in the common/serializedEvent directory.

This directory contains serializedEvent payload shapes for adding additional information to events. The classes here have a name for
the serializedEvent type they represent, and then remaining fields are meant to be serialized into json and stored in the payload
attribute of an serializedEvent when written to the serializedEvent store. This allows for all events to have the necessary attributes for 
serializedEvent sourcing, while giving us a flexible payload field to pass additional information with the serializedEvent.