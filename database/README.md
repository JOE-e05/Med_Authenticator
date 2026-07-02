# Database Migrations

Run migration files in the order they appear in this folder.

## Current baseline

1. `20260702_01_manufacturer_overhaul.sql`
2. `20260702_02_performance_indexes.sql`

## How to apply

1. Open your MySQL client connected to the `system database` schema.
2. Execute each migration file once.
3. Confirm expected tables and columns were created.

## Notes

- This project currently has no automated migration runner.
- Keep every migration additive and idempotent where possible.

## Optional seed data (for quick local testing)

1. `database/seeds/20260702_01_quick_smoke_seed.sql`

After applying migrations, import this seed file to get demo users and sample verification/report data for smoke tests.

Seed login credentials (all use password `Test1234`):

- Patient: `patient.demo@med.local`
- Pharmacist: `pharmacist.demo@med.local`
- Regulator: `regulator.demo@med.local`
- Approved Manufacturer: `manufacturer.approved@med.local`
- Pending Manufacturer: `manufacturer.pending@med.local`

Admin login still uses the existing `administrator` table in your environment.
