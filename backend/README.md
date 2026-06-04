# Financial ERP Backend

This backend is a Laravel 11/Inertia-ready implementation scaffold for the existing project tracker frontend and the requested deterministic Financial ERP engine.

## Built Modules

- Multi-tenant context resolution, global Eloquent scope, and tenant auto-assignment trait.
- General ledger chart of accounts, journal entries, and journal lines.
- Accounts payable suppliers, invoices, and approval steps.
- Accounts receivable customers, invoices, and payments.
- Project workspace tables for the existing Vue project dashboard footprint.
- Structured query filtering service.
- Transaction-safe posting services.
- Audit package queue job and ZIP manifest compiler.
- Deterministic rule evaluator for anomalous journal line state transitions.
- Signed conversational webhook gateway.
- PostgreSQL recursive CTE graph service.

## Integration Note

The current frontend is a standalone Vite/Vue Router app. This backend returns Inertia page responses and expects the frontend pages to be migrated or mirrored under Laravel `resources/js/Pages`.
