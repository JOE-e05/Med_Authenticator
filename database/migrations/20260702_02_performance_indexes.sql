-- 20260702_02_performance_indexes.sql
-- Performance indexes for verification and investigation workflows.

START TRANSACTION;

-- Speeds up role-based history lookups and recent activity timelines.
ALTER TABLE verification_log
    ADD INDEX idx_verification_user_time (userID, verified_at),
    ADD INDEX idx_verification_actor_user_time (actor_role, userID, verified_at),
    ADD INDEX idx_verification_code_time (batchNumber, verified_at),
    ADD INDEX idx_verification_result_time (result, verified_at);

-- Speeds up regulator investigation feed filters and auto-alert duplicate checks.
ALTER TABLE report
    ADD INDEX idx_report_status_time (status, reported_at),
    ADD INDEX idx_report_source_time (source_type, reported_at),
    ADD INDEX idx_report_user_code_source_time (userID, batchNumber, source_type, reported_at),
    ADD INDEX idx_report_verification_log (verification_log_id);

-- Speeds up patient history joins and manufacturer dashboards.
ALTER TABLE medicine_pack_codes
    ADD INDEX idx_pack_code_batch (pack_code, batch_id);

ALTER TABLE medicine_batches
    ADD INDEX idx_batches_manufacturer_time (manufacturer_user_id, created_at),
    ADD INDEX idx_batches_name_time (med_name, created_at);

COMMIT;
