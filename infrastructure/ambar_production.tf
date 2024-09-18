#resource "ambar_data_source" "identity_event_store" {
#  data_source_type = "postgres"
#  description      = "ledger"
#
#  data_source_config = {
#    "hostname"                   = module.production_identity.connection_outputs["event_store_proxy_endpoint"]
#    "tlsTerminationOverrideHost" = module.production_identity.connection_outputs["event_store_proxy_endpoint_domain"]
#    "hostPort"                   = module.production_identity.connection_outputs["event_store_port"]
#    "databaseName"               = module.production_identity.connection_outputs["event_store_database_name"]
#    "username"                   = module.production_identity.connection_outputs["event_store_user"]
#    "password"                   = module.production_identity.connection_outputs["event_store_password"]
#    "publicationName"            = module.production_identity.publication_name
#    "tableName"                  = module.production_identity.ledger_table_name
#    "columns"                    = "id,event_id,aggregate_id,aggregate_version,causation_id,correlation_id,recorded_on,event_name,json_payload,json_metadata"
#    "partitioningColumn"         = "aggregate_id"
#    "serialColumn"               = "id"
#  }
#}