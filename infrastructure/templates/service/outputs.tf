output "connection_outputs" {
  value = {
    application_domain                         = module.application.cloudrun_public_https_endpoint
    event_store_database_name                  = module.event_store.database_name
    event_store_public_ip                      = module.event_store.database_public_ip_address
    event_store_private_ip                     = module.event_store.database_private_ip_address
    event_store_password                       = module.event_store.database_admin_password
    event_store_port                           = module.event_store.database_port
    event_store_user                           = module.event_store.database_admin_username
    event_store_table_name                     = module.application.event_store_table_name
    event_store_replication_username           = module.application.event_store_replication_username
    event_store_replication_password           = module.application.event_store_replication_password
    event_store_replication_publication_name   = module.application.event_store_replication_publication_name
    event_store_proxy_endpoint                 = module.pgt_proxy.public_ip
    event_store_proxy_endpoint_domain          = var.pgt_proxy_cert_common_name
    mongodb_projection_authentication_database = module.projection_and_reaction_store.authentication_database
    mongodb_projection_database_name           = module.application.projection_database_name
    mongodb_projection_database_password       = module.projection_and_reaction_store.admin_password
    mongodb_projection_database_username       = module.projection_and_reaction_store.admin_username
    mongodb_projection_host                    = module.projection_and_reaction_store.database_public_ip
    mongodb_projection_port                    = module.projection_and_reaction_store.database_port
    mongodb_reaction_authentication_database   = module.projection_and_reaction_store.authentication_database
    mongodb_reaction_database_name             = module.application.reaction_database_name
    mongodb_reaction_database_password         = module.projection_and_reaction_store.admin_password
    mongodb_reaction_database_username         = module.projection_and_reaction_store.admin_username
    mongodb_reaction_host                      = module.projection_and_reaction_store.database_public_ip
    mongodb_reaction_port                      = module.projection_and_reaction_store.database_port
    session_tokens_expire_after_seconds        = 72000
  }
}

output public_domain {
  value = module.application.cloudrun_public_https_endpoint
}