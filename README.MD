# Event Sourcing Course - Example

This repository contains an example event sourced application, used in Ambar's Event Sourcing course. You can join 
the course for free [in this link](https://ambar.cloud/esd). Please retake the course as many times as you need!

## Contents

The example contains an event sourced financial institution that allows you to:

1. Sign up
2. Verify your email
3. Sign in / sign out
4. View credit card products you can apply to (platinum and starter)

The application is written in PHP, but we will soon have the session aggregate implemented in multiple languages.

### Getting Started

To run this application you need Docker. Once you have Docker installed, please clone the code, 
navigate to `local-development` and run `dev_start.sh`. You will receive further instructions.

```bash
git clone git@github.com:ambarltd/courses-v2.git
cd courses-v2
./dev_start.sh
```

### Making Sense of the Code

To make sense of this application, please re-review the slides and recordings you received during your course. 
For now, here's the directory structure of the project:

```markdown

application/                # Source code
├── frontend-javascript/    # Source code for the frontend
└── backend-php/            # Source code for the backend

local-development/          # Local development environment defined with Docker Compose
│
│
├── docker-compose.yml      # Docker Compose definition
│                           #     Includes an event store (Postgres), projection db (Mongo),
│                           #     backend (PHP + Nginx), frontend (JavaScript + Nginx),
│                           #     and an Ambar emulator (for projections and reactions)
│
│
├── ambar-config.yml        # Configuration for Ambar emulator
│                           #     Needed for projections and reactions as per 
│                           #     https://hub.docker.com/r/ambarltd/emulator
│
├── dev_start.sh            # Starts the application's containers and gives you first steps!
└── dev_shutdown.sh         # Stops the application's Docker containers
```

### Support

If you get stuck, please ask us questions in the #event-sourcing channel of our Slack community. 
If you're not part of our Slack, please sign up [here](https://www.launchpass.com/ambar). 
Or if you'd like a free private walkthrough, simply book one [here](https://calendly.com/luis-ambar).

