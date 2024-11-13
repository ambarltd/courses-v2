module "networking" {
  source                                           = "./../networking"
  resource_id_prefix                               = "${local.resource_id_prefix_prefix}-net"
  public_cidrs_with_mongo_port_access_to_instances = ["0.0.0.0/0"]
  public_cidrs_with_pg_port_access_to_instances    = ["0.0.0.0/0"]
  public_cidrs_with_ssh_port_access_to_instances   = ["0.0.0.0/0"]
}

module "event_store" {
  source                         = "./../event-store"
  resource_id_prefix             = "${local.resource_id_prefix_prefix}-es"
  network_id_with_private_access = module.networking.network_id
  public_cidrs_with_access       = ["0.0.0.0/0"]
}

module "application" {
  source                                     = "./../application"
  resource_id_prefix                         = "${local.resource_id_prefix_prefix}-app"
  application_directory_name                 = var.application_directory_name
  git_commit_hash                            = var.git_commit_hash
  vpc_connector_subnetwork_name              = module.networking.vpc_connector_name
  event_store_database_name                  = module.event_store.database_name
  event_store_table_name                     = "event_store"
  event_store_host                           = module.event_store.database_private_ip_address
  event_store_password                       = module.event_store.database_admin_password
  event_store_port                           = module.event_store.database_port
  event_store_user                           = module.event_store.database_admin_username
  mongodb_projection_authentication_database = module.projection_and_reaction_store.authentication_database
  mongodb_projection_database_name           = "projections"
  mongodb_projection_database_password       = module.projection_and_reaction_store.admin_password
  mongodb_projection_database_username       = module.projection_and_reaction_store.admin_username
  mongodb_projection_host                    = module.projection_and_reaction_store.database_private_ip
  mongodb_projection_port                    = module.projection_and_reaction_store.database_port
  mongodb_reaction_authentication_database   = module.projection_and_reaction_store.authentication_database
  mongodb_reaction_database_name             = "reactions"
  mongodb_reaction_database_password         = module.projection_and_reaction_store.admin_password
  mongodb_reaction_database_username         = module.projection_and_reaction_store.admin_username
  mongodb_reaction_host                      = module.projection_and_reaction_store.database_private_ip
  mongodb_reaction_port                      = module.projection_and_reaction_store.database_port
  session_tokens_expire_after_seconds        = 72000
  full_service_name_in_lowercase             = var.full_service_name_in_lowercase
}

module "pgt_proxy" {
  source                               = "./../pgt-proxy"
  resource_id_prefix                   = "${local.resource_id_prefix_prefix}-pgtp"
  pgtproxy_cert_in_base64              = var.pgtproxy_cert_in_base64
  pgtproxy_key_in_base64               = var.pgtproxy_key_in_base64
  network_id_with_destination_database = module.networking.network_id
  subnetwork_id                        = module.networking.subnetwork_id_1
  database_ca_cert_in_base64           = module.event_store.database_ca_cert_in_base64
  database_local_network_ip_address    = module.event_store.database_private_ip_address
}

module "projection_and_reaction_store" {
  source             = "./../projection-and-reaction-db"
  resource_id_prefix = "${local.resource_id_prefix_prefix}-pard"
  network_name       = module.networking.network_name
  subnetwork_name    = module.networking.subnetwork1_name
}
