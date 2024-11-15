This is the java implementation of the backend services for the Ambar course repo. This portion of the application 
is structured as follows:
```
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

For more details on relating these directories and their classes back to CQRS and EventSource see the READMEs found 
throughout the application.