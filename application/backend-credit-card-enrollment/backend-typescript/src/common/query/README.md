# Query Handler

A Query Handler in an EventSourcing system is responsible for taking requests for information (queries) from end users
or other systems (both internal and external), validating the query (e.g., checking if a user has the right 
permissions), and returning said information. 

To do this, Query Handlers will read state from read model / projection databases. Those databases are _filled_ up 
by Projections (see Projection directory).

### Advantages:

* Performance: Since queries access a read-optimized database, response times are faster and more efficient.
* Scalability: The query data storage (projection/read model databases) can be scaled separately from the Event Store
used in Command Handlers.
* Flexibility: Different read models can be tailored for various use cases, offering specialized views for reporting, 
analytics, or specific user interfaces.