resource "random_id" "production_uuid" {
  byte_length = 6
}

module "production_identity" {
  source                     = "./templates/service"
  environment_uuid           = "a${random_id.production_uuid.hex}-pro"
  service_name               = "ide"
  git_commit_hash            = var.git_commit_hash
  pgt_proxy_cert_common_name = local.credentials["pgt_proxy_certificate_common_name"]
  pgtproxy_cert_in_base64    = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64     = local.credentials["pgtproxy_key_in_base64"]
  application_directory_name = "monorepo_for_all_services"
  full_service_name_in_lowercase = "identity"

  providers = {
    google = google.production
  }
}

module "production_security" {
  source                     = "./templates/service"
  environment_uuid           = "a${random_id.production_uuid.hex}-pro"
  service_name               = "sec"
  git_commit_hash            = var.git_commit_hash
  pgt_proxy_cert_common_name = local.credentials["pgt_proxy_certificate_common_name"]
  pgtproxy_cert_in_base64    = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64     = local.credentials["pgtproxy_key_in_base64"]
  application_directory_name = "monorepo_for_all_services"
  full_service_name_in_lowercase = "security"

  providers = {
    google = google.production
  }
}

module "production_frontend" {
  source = "./templates/frontend-service"
  application_directory_name = "frontend"
  environment_uuid           = "a${random_id.production_uuid.hex}-pro"
  git_commit_hash = var.git_commit_hash
  service_name = "fro"

  providers = {
    google = google.production
  }
  domain_identity = module.production_identity.public_domain
  domain_security = module.production_security.public_domain
}

output "production_backend_connection_outputs" {
  value = {
    "identity" = module.production_identity.connection_outputs
    "security" = module.production_security.connection_outputs
  }
  sensitive = true
}

output "public_domains" {
  value = {
    "frontend" = module.production_frontend.public_domain
    "identity" = module.production_identity.public_domain
    "security" = module.production_security.public_domain
  }
}