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

module "production_credit_card_product" {
  source                     = "./templates/service"
  environment_uuid           = "a${random_id.production_uuid.hex}-pro"
  service_name               = "ccp"
  git_commit_hash            = var.git_commit_hash
  pgt_proxy_cert_common_name = local.credentials["pgt_proxy_certificate_common_name"]
  pgtproxy_cert_in_base64    = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64     = local.credentials["pgtproxy_key_in_base64"]
  application_directory_name = "monorepo_for_all_services"
  full_service_name_in_lowercase = "credit_card_product"

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
  domain_identity = module.production_identity.public_domain
  domain_security = module.production_security.public_domain

  providers = {
    google = google.production
  }
}

module "production_ambar" {
  source = "./ambar/production"
  data_source_identity = {
    "hostname"                   = module.production_identity.connection_outputs["event_store_proxy_endpoint_domain"]
    "endpoint"                   = module.production_identity.connection_outputs["event_store_proxy_endpoint"]
    "port"                       = module.production_identity.connection_outputs["event_store_port"]
    "database"                   = module.production_identity.connection_outputs["event_store_database_name"]
    "username"                   = module.production_identity.connection_outputs["event_store_replication_username"]
    "password"                   = module.production_identity.connection_outputs["event_store_replication_password"]
    "publicationName"            = module.production_identity.connection_outputs["event_store_replication_publication_name"]
    "tableName"                  = module.production_identity.connection_outputs["event_store_table_name"]
    "columns"                    = "id,event_id,aggregate_id,aggregate_version,causation_id,correlation_id,recorded_on,event_name,json_payload,json_metadata"
    "partitioningColumn"         = "correlation_id"
    "serialColumn"               = "id"
  }
  data_source_security = {
    "hostname"                   = module.production_security.connection_outputs["event_store_proxy_endpoint_domain"]
    "endpoint"                   = module.production_security.connection_outputs["event_store_proxy_endpoint"]
    "port"                       = module.production_security.connection_outputs["event_store_port"]
    "database"                   = module.production_security.connection_outputs["event_store_database_name"]
    "username"                   = module.production_security.connection_outputs["event_store_replication_username"]
    "password"                   = module.production_security.connection_outputs["event_store_replication_password"]
    "publicationName"            = module.production_security.connection_outputs["event_store_replication_publication_name"]
    "tableName"                  = module.production_security.connection_outputs["event_store_table_name"]
    "columns"                    = "id,event_id,aggregate_id,aggregate_version,causation_id,correlation_id,recorded_on,event_name,json_payload,json_metadata"
    "partitioningColumn"         = "correlation_id"
    "serialColumn"               = "id"
  }
  data_source_credit_card_product = {
    "hostname"                   = module.production_credit_card_product.connection_outputs["event_store_proxy_endpoint_domain"]
    "endpoint"                   = module.production_credit_card_product.connection_outputs["event_store_proxy_endpoint"]
    "port"                       = module.production_credit_card_product.connection_outputs["event_store_port"]
    "database"                   = module.production_credit_card_product.connection_outputs["event_store_database_name"]
    "username"                   = module.production_credit_card_product.connection_outputs["event_store_replication_username"]
    "password"                   = module.production_credit_card_product.connection_outputs["event_store_replication_password"]
    "publicationName"            = module.production_credit_card_product.connection_outputs["event_store_replication_publication_name"]
    "tableName"                  = module.production_credit_card_product.connection_outputs["event_store_table_name"]
    "columns"                    = "id,event_id,aggregate_id,aggregate_version,causation_id,correlation_id,recorded_on,event_name,json_payload,json_metadata"
    "partitioningColumn"         = "correlation_id"
    "serialColumn"               = "id"
  }
  data_destination_identity            = {
    endpoint_prefix: module.production_identity.public_domain
  }
  data_destination_security            = {
    endpoint_prefix: module.production_security.public_domain
  }
  data_destination_credit_card_product = {
    endpoint_prefix: module.production_credit_card_product.public_domain
  }
  providers = {
    ambar = ambar.production
  }
}

output "production_backend_connection_outputs" {
  value = {
    "frontend" = module.production_frontend.public_domain
    "identity" = module.production_identity.connection_outputs
    "security" = module.production_security.connection_outputs
    "credit_card_product" = module.production_credit_card_product.connection_outputs
  }
  sensitive = true
}

output "public_domains" {
  value = {
    "frontend" = module.production_frontend.public_domain
    "identity" = module.production_identity.public_domain
    "security" = module.production_security.public_domain
    "credit_card_product" = module.production_credit_card_product.public_domain
  }
}