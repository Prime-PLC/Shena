-- Allow a provider to serve several stages without changing existing assignments.
ALTER TABLE claim_provider_assignments DROP FOREIGN KEY fk_assignment_provider_stage;
ALTER TABLE claim_provider_assignments DROP INDEX uq_claim_provider;
ALTER TABLE claim_provider_assignments ADD CONSTRAINT fk_assignment_provider FOREIGN KEY (provider_id) REFERENCES service_providers(id);
ALTER TABLE service_providers DROP INDEX uq_provider_identity, DROP INDEX uq_provider_phone;
