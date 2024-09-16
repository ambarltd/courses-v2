resource "random_id" "production_uuid" {
  byte_length = 6
}

module "production_monolith" {
  source                     = "./templates/service"
  environment_uuid           = "a${random_id.production_uuid.hex}-pro"
  service_name               = "mon"
  git_commit_hash            = var.git_commit_hash
  pgt_proxy_cert_common_name = local.credentials["pgt_proxy_certificate_common_name"]
  pgtproxy_cert_in_base64    = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64     = local.credentials["pgtproxy_key_in_base64"]
  application_directory_name = "monolith"

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
}

output "production_connection_outputs" {
  value = {
    "monolith" = module.production_monolith.connection_outputs
    "frontend" = module.production_frontend.connection_outputs
  }
  sensitive = true
}