In an Event Sourcing and CQRS (Command Query Responsibility Segregation) system, queries are used to retrieve data without 
directly interacting with the write-side of the system. Queries are directed at read models, which are projections built 
from events to provide a fast and efficient way to access the current state of the data.

### Role of Queries in CQRS:
In CQRS, the system separates commands (which modify state) from queries (which retrieve state). Queries operate on the 
read model, a database or projection that has been constructed from the event stream to provide optimized views of the 
system’s data. This enables high-performance reads without impacting the write-side performance.

### How Queries Work:
1. Read Models: Queries are made against read models, which are updated in real-time as events occur. These read models are 
often denormalized and structured for the specific needs of the queries, making them more efficient for reading data.

2. No Business Logic: Unlike the write-side, queries do not involve business logic or validation; they simply return the 
current state of the data as represented in the read model.

3. Separation of Concerns: The separation of querying from command handling allows the system to scale independently for 
reads and writes. It also enables the use of different database technologies for the read side, optimized for query 
performance.

### Advantages:
* Performance: Since queries access a read-optimized database, response times are faster and more efficient.
* Scalability: The read and write sides can be scaled separately, allowing the system to handle large volumes of reads 
without burdening the write-side processing.
* Flexibility: Different read models can be tailored for various use cases, offering specialized views for reporting, 
analytics, or specific user interfaces.