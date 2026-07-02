# Database Migrations

Run migration files in the order they appear in this folder.

## Current baseline

1. `20260702_01_manufacturer_overhaul.sql`

## How to apply

1. Open your MySQL client connected to the `system database` schema.
2. Execute each migration file once.
3. Confirm expected tables and columns were created.

## Notes

- This project currently has no automated migration runner.
- Keep every migration additive and idempotent where possible.
