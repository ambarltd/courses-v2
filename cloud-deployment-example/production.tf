resource "random_id" "production_uuid" {
  byte_length = 6
}

module "production_backend_php" {
  source                     = "./templates/service"
  environment_uuid           = "c${random_id.production_uuid.hex}-pro"
  service_name               = "bep"
  git_commit_hash            = var.git_commit_hash
  pgt_proxy_cert_common_name = local.credentials["pgt_proxy_certificate_common_name"]
  pgtproxy_cert_in_base64    = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64     = local.credentials["pgtproxy_key_in_base64"]
  application_directory_name = "backend-php"
  full_service_name_in_lowercase = "backend_php"

  providers = {
    google = google.production
  }
}

module "production_frontend" {
  source = "./templates/frontend-service"
  application_directory_name = "frontend"
  environment_uuid           = "c${random_id.production_uuid.hex}-pro"
  git_commit_hash = var.git_commit_hash
  service_name = "fro"
  domain_identity = module.production_backend_php.public_domain
  domain_security = module.production_backend_php.public_domain
  domain_credit_card_product = module.production_backend_php.public_domain

  providers = {
    google = google.production
  }
}

module "production_ambar" {
  source = "./ambar/production"
  data_source_event_store = {
    "hostname"                   = module.production_backend_php.connection_outputs["event_store_proxy_endpoint_domain"]
    "endpoint"                   = module.production_backend_php.connection_outputs["event_store_proxy_endpoint"]
    "port"                       = module.production_backend_php.connection_outputs["event_store_port"]
    "database"                   = module.production_backend_php.connection_outputs["event_store_database_name"]
    "username"                   = module.production_backend_php.connection_outputs["event_store_replication_username"]
    "password"                   = module.production_backend_php.connection_outputs["event_store_replication_password"]
    "publicationName"            = module.production_backend_php.connection_outputs["event_store_replication_publication_name"]
    "tableName"                  = module.production_backend_php.connection_outputs["event_store_table_name"]
    "columns"                    = "id,event_id,aggregate_id,aggregate_version,causation_id,correlation_id,recorded_on,event_name,json_payload,json_metadata"
    "partitioningColumn"         = "correlation_id"
    "serialColumn"               = "id"
  }
  data_destination_backend_php            = {
    endpoint_prefix: module.production_backend_php.public_domain
  }
  providers = {
    ambar = ambar.production
  }
}

output "production_backend_connection_outputs" {
  value = {
    "frontend" = module.production_frontend.public_domain
    "identity" = module.production_backend_php.connection_outputs
    "security" = module.production_backend_php.connection_outputs
    "credit_card_product" = module.production_backend_php.connection_outputs
  }
  sensitive = true
}

output "public_domains" {
  value = {
    "frontend" = module.production_frontend.public_domain
    "identity" = module.production_backend_php.public_domain
    "security" = module.production_backend_php.public_domain
    "credit_card_product" = module.production_backend_php.public_domain
  }
}