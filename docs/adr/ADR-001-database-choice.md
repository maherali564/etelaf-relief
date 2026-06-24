# ADR-001: Database Choice

## Status
Accepted

## Context
The project needed a database that works out of the box for development while supporting production-grade MySQL.

## Decision
Use SQLite as the default for development/testing and MySQL for production. Both configurations are maintained in config/database.php.

## Consequences
- Easy setup for developers (no external DB server needed)
- Migration scripts must work on both SQLite and MySQL
- Production deployment requires switching .env values
