# Java Example

This is the Java Implementation Example in Ambar's event sourcing course. While there is a PHP implementation
for basic services such as Identity (contains the User aggregate), Security (contains the Session aggregate),
and Credit Card / Product (creates and manages credit card products), this Java implementation is focused on
the Enrollment aggregate (i.e., the process of applying for a Credit Card Product).

```
src/main/java/cloud/....

ambar/
├── common/     # Has common components for the java application. Things that are required throughout the application
│               # such as database setup and access, exceptions, or common base classes such as for events. 
│
└── creditcard/ # The business functionality of our application, with each child directory containing
    │           # source code for aggregates, command handlers, events, queries, projections, and reactions related 
    |           # to that portion of the application.
    │
    ├── enrollment/     # Is where source code relating to users enrolling for a credit card product, and we 
    │                   # will handle related events, projections, reactions, and queries.
    │
    └── product/        # Because we're using events from the PHP portion of the application, we need to have
                        # classes for those events so we can use them in our Java application (e.g., build a projection).
```

## Documentation

For more details on how we use aggregates, events, command handlers, queries, projections, and 
reactions in this event sourcing example, please check the READMEs inside `common/aggregate`,
`common/event`, `common/commandhandler`, `common/eventstore`, etc. These directories are 
located in `src/main/java/cloud/ambar/common`.

### About this Implementation

This application is built using maven and all of its dependencies, plugins, and build process can be 
found in the `pom.xml` file.

We leverage Spring Boot + Spring Boot Web as a basis for our application which provide some basic features 
around logging, configuration, and dependency injection. Spring Boot simplifies our setup for a web server 
to host endpoints to interact with our application and handle the serialization and deserialization of the
request response flow.

We leverage a Postgres dependency to interact with the Event Store. We leverage a MongoDB dependency to
interact with Projections.

Finally, lombok is used extensively throughout the codebase to reduce repetitive boilerplate code to help
emphasize business logic and reduce distractions.