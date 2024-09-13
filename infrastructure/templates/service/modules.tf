module "networking" {
  source = "./../networking"
  resource_id_prefix = "${local.resource_id_prefix_prefix}-net"
}

module "event_store" {
  source = "./../event-store"
  resource_id_prefix = "${local.resource_id_prefix_prefix}-es"
  network_id_with_private_access = module.networking.network_id
  public_cidr_ranges_with_access = []
}

module "application" {
  source = "./../application"
  resource_id_prefix = "${local.resource_id_prefix_prefix}-app"
  application_directory_name = var.application_directory_name
  git_commit_hash = var.git_commit_hash
  event_store_pg_database = module.event_store.database_name
  event_store_pg_host = module.event_store.database_private_ip_address
  event_store_pg_username = module.event_store.database_admin_username
  event_store_pg_password = module.event_store.database_admin_password
  event_store_pg_port = "5432"
  vpc_connector_subnetwork_name = module.networking.vpc_connector_name
}

module "pgt_proxy" {
  source = "./../pgt-proxy"
  resource_id_prefix = "${local.resource_id_prefix_prefix}-pgtp"
  pgtproxy_cert_in_base64 = var.pgtproxy_cert_in_base64
  pgtproxy_key_in_base64 = var.pgtproxy_key_in_base64
  network_id_with_destination_database = module.networking.network_id
  subnetwork_id = module.networking.subnetwork_id_1
  database_ca_cert_in_base64 = module.event_store.database_ca_cert_in_base64
  database_local_network_ip_address = module.event_store.database_private_ip_address
}

module "projection_and_reaction_store" {
  source = "./../projection-and-reaction-db"
  resource_id_prefix = "${local.resource_id_prefix_prefix}-pard"
  network_name = module.networking.network_name
  subnetwork_name = module.networking.subnetwork1_name
}
