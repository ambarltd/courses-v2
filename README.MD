# Notes

This repo is for the Ambar Event Sourcing / Microservices courses. 
You can use this repo to deploy a real world credit card application which leverages both event sourcing and microservices 
to create a larger application. Note that this is an academic example, implemented in multiple languages and with multiple 
components - you should not expect to deeply understand everything in all the languages.

## Preparation Steps

These steps will be performed with your instructor during the first coding session of your course, and will allow you to
deploy this repo into a cloud environment running on Google Cloud Platform (GCP)

1. Fork this repository
2. Create two GitHub action secrets (provided by your course instructor) as follows:
```
STATE_MANAGEMENT_BASE64=some_string_here
CREDENTIALS_BASE64=some_string_here
```
**N.B. Make sure to enter the entire string for each secret, with no quotes, and no newlines!**
3. Make a trivial commit (such as updating this readme), and push it to GitHub.

## Extending this program

Our sample application attempts to model a financial institution which offers different credit cards (products). We have
already modeled some services of the application - some multiple times in multiple languages! To get started, you should
look at one of the already modeled services like the Credit-Card-Product service which is implemented in multiple languages
and try to follow along in one of the languages you are familiar with (php, java). Reimplement the service in your language
of choice, or add any missing features to the module.

Once you have an understanding of the pattern as it works in your chosen implementation language, feel free to implement
another service of the application. If you feel inclined, open a pull request to the Ambar repo, and we will review and merge 
it and extend the frontend application to leverage the capabilities!

### Event Sourcing Additional Context / Reminders

A quick reminder on Event Sourcing

Event Sourcing is the inversion of state in an application. Instead of directly storing state in a record and performing CRUD
operations on those records, we record series of events that describe what happened in our application and derive state from
one or more of those events. This gives us a direct historical event sequence that we can use to build up state in many ways
and to even 'time travel' to see what state was in the past, without the need for audit tables or trying to deal with the
dual write problem.

Note: This does not mean we do not directly store state in event sourcing! We can still distill information from our events
to build useful models about the state of our application and its data. This is where projections and read models come in.

An easy way to organize event sourcing conceptually, and in code, is by leveraging the Command Query Responsibility Separation
pattern (CQRS) where we create distinct paths for our write (record events) and read (leverage events / state) actions.

#### Terms refresher

* **Command**: A request for something to happen in our application.
  * E.G. Making a new credit card product for customers to apply for.
* **Event**: A recording of something significant happening in our application containing things like who, what, and when.
Events should be small and precise, and are written sequentially to an immutable log. Never change events or insert new ones 
except at the tail of the log.
  * E.G. Activating a credit card product so customers can apply for it 
(Who: The product ID, What: Make it active, When: when was the event recorded)

### Microservices Additional Context / Reminders