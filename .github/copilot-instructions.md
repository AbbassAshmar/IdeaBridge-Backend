# IdeaBridge Copilot Instructions

These instructions are always-on for this repository and define how new features and refactors must be implemented.

## Architecture Overview

The backend follows a layered modular architecture:

- Module routes register endpoints in `app/Modules/<Module>/Routes/api.php`.
- Controllers are thin orchestration only (HTTP input/output boundary).
- Services hold business logic and orchestration between repositories.
- Repositories own all ORM/database query logic and data mapping.
- Models define relationships and persistence metadata.
- Global exception handling and API error rendering are centralized in `bootstrap/app.php`.

Root API registration happens in `routes/api.php` by grouping each module route file.

## Required Layer Responsibilities

### Controllers

- Keep controllers thin.
- Do not place business logic in controllers.
- Validate input using Form Requests.
- Call service methods and return `ResponseHelper::success(...)` envelopes.
- Do not catch domain/repository exceptions in controllers.

### Services

- Service layer is pure business logic.
- Services must know nothing about HTTP request/response details.
- Services call repositories for persistence/query access.
- In services, throw only exceptions derived from `DomainError`.

### Repositories

- Repositories own Eloquent/ORM and SQL concerns.
- Handle filtering, sorting, pagination, eager loading, aggregation, and query execution.
- Prevent N+1 by using `with(...)`, `withCount(...)`, subselects, and constrained eager loading as needed.
- In repositories, throw only exceptions derived from `RepositoryError`.

## Error Handling Rules

- `ApplicationError` is the base for structured app errors.
- Domain-layer errors must extend `DomainError` (for example, `IdeasDomainError`).
- Data-access errors must extend `RepositoryError` (for example, `IdeaRepositoryError`).
- If adding a new exception type, ensure it is rendered correctly by the global exception pipeline in `bootstrap/app.php`.
- Controllers should not catch these errors; allow them to bubble to the exception handler.

## API Documentation Rules

- OpenAPI documentation lives in `app/OpenApi/ApiDocumentation.php`.
- Every new or changed endpoint must be reflected in OpenAPI annotations.
- Keep request schema, response envelopes, status codes, and auth/permission requirements in sync with route definitions.
- Reuse existing schema components (`UserResource`, `CategoryResource`, `IdeaResource`, error envelopes) when possible.

## Auth and Permissions

- Use `auth:sanctum` middleware for authenticated endpoints.
- Enforce permissions at route level using Spatie middleware (for example, `permission:add idea`).
- Keep permission names consistent across route files, seeders, and docs.

## Data and Response Conventions

- Follow existing response envelope shape via `ResponseHelper`:
  - `data`
  - `error`
  - `meta`
- Keep mapping/serialization shape stable for clients.
- Use API Resources when shaping complex payloads.

## Feature Development Checklist

When implementing a new backend feature:

1. Add or update migration(s) and model relationships.
2. Add/update Form Request validation.
3. Extend repository interface and implementation.
4. Add business use case logic to service.
5. Keep controller minimal and wire route(s).
6. Add/adjust permission(s) and seeder mappings if required.
7. Update OpenAPI documentation in `app/OpenApi/ApiDocumentation.php`.
8. Validate routes and run targeted tests/checks.

## Refactoring Guardrails

- Preserve public API contracts unless a change is explicitly requested.
- Prefer incremental, module-local refactors.
- Avoid cross-layer leakage (no HTTP in service/repository, no ORM query logic in controller/service).
- Keep logs useful and contextual in services/repositories.
