# Session Authentication

This directory contains an API to transform a session token into a user ID. This is fetched from a projection database,
but note that the _filling up_ of that projection database is done by another service, so this codebase
only needs to read the session read model / projection database, not update it. 