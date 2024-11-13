resource "ambar_data_source" "event_store" {
  data_source_type = "postgres"
  description      = "event_store"

  data_source_config = {
    "hostname"                   = var.data_source_event_store.hostname
    "tlsTerminationOverrideHost" = var.data_source_event_store.endpoint
    "hostPort"                   = var.data_source_event_store.port
    "databaseName"               = var.data_source_event_store.database
    "username"                   = var.data_source_event_store.username
    "password"                   = var.data_source_event_store.password
    "publicationName"            = var.data_source_event_store.publicationName
    "tableName"                  = var.data_source_event_store.tableName
    "columns"                    = var.data_source_event_store.columns
    "partitioningColumn"         = var.data_source_event_store.partitioningColumn
    "serialColumn"               = var.data_source_event_store.serialColumn
  }
}
