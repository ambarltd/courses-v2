resource "random_string" "environment_id" {
  length      = 8
  min_lower   = 2
  min_upper   = 2
  min_numeric = 2
  special     = false
  min_special = 0
}

locals {
  environment_name = "production-${lower(random_string.environment_id.result)}"
}

module "networking" {
  source = "./../templates/networking"
  environment_name = local.environment_name
}

module "event_store" {
  source = "./../templates/event-store"
  environment_name = local.environment_name
  network_id_with_private_access = module.networking.network_id
  public_cidr_ranges_with_access = []
}

module "application" {
  source = "./../templates/application"
  environment_name = local.environment_name
  application_directory_name = "http-server"
  git_commit_hash = var.git_commit_hash
  event_store_pg_database = module.event_store.database_name
  event_store_pg_host = module.event_store.database_private_ip_address
  event_store_pg_username = module.event_store.database_admin_username
  event_store_pg_password = module.event_store.database_admin_password
  event_store_pg_port = "5432"
  network_connector_cidr = "10.0.2.128/30"
  network_name = module.networking.network_id
  network_region = module.networking.network_region
}

module "pgt_proxy" {
  source = "./../templates/pgt-proxy"
  environment_name = local.environment_name
  pgtproxy_cert_in_base64 = var.pgtproxy_cert_in_base64
  pgtproxy_key_in_base64 = var.pgtproxy_key_in_base64
  network_id_with_destination_database = module.networking.network_id
  subnetwork_id = module.networking.subnetwork_id_1
  database_ca_cert_in_base64 = module.event_store.database_ca_cert_in_base64
  database_local_network_ip_address = module.event_store.database_private_ip_address
}

module "projection_store" {
  source = "./../templates/projection-store"
  environment_name = local.environment_name
}
