This is the java implementation of the backend services for the Ambar course repo. This portion of the application 
is structured as follows:
```
src/main/java/cloud/....

ambar/
├── common/     # Has common components for the java application. Things that are required throughout the application
│               # such as database setup and access, exceptions, or common base classes such as for events. You can also
│               # find models used when comunicating with Ambar.
│
└── product/    # Is where we will put the business functionality of our application, with each child directory containing
    │           # source code for commands, events, queries, projections, and reactions related to that portion of the 
    │           # application.
    │
    ├── enrollment/     # Is where source code relating to users enrolling (applying) for a credit card product, and we 
    │                   # will handle related events, projections, reactions, and queries.
    │
    └── management/     # Is where source code relating to creating, activating, updating, and generally managing credit
                        # card products exists, along with related events, projections, and queries.
    
```

For more details on relating these directories and their classes back to CQRS, EventSourcing and our course topics, see 
the READMEs found throughout the application.

Some more details on the java application...
This application is built using maven and all of its dependencies, plugins, and build process can be found in the `pom.xml` 
file.

We leverage Spring Boot + Spring Boot Web as a basis for our application which provide some basic  features around logging,
configuration, and application lifecycle management. The web portion also simplifies our setup for a web server to host rest
endpoints to interact with our application and handle the serialization and deserialization of the request response flow.

Spring JPA (Java Persistence API) and Mongo / Postgres dependencies to be able to connect to and leverage MongoDb and 
PostgreSql for database storage systems for our events and read models.

Finally, lombok is used extensively throughout the codebase to reduce repetitive boilerplate code to help emphasize business 
logic and reduce distractions.