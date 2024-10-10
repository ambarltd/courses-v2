resource "ambar_data_source" "identity_event_store" {
  data_source_type = "postgres"
  description      = "identity_event_store"

  data_source_config = {
    "hostname"                   = var.data_source_identity.hostname
    "tlsTerminationOverrideHost" = var.data_source_identity.endpoint
    "hostPort"                   = var.data_source_identity.port
    "databaseName"               = var.data_source_identity.database
    "username"                   = var.data_source_identity.username
    "password"                   = var.data_source_identity.password
    "publicationName"            = var.data_source_identity.publicationName
    "tableName"                  = var.data_source_identity.tableName
    "columns"                    = var.data_source_identity.columns
    "partitioningColumn"         = var.data_source_identity.partitioningColumn
    "serialColumn"               = var.data_source_identity.serialColumn
  }
}

resource "ambar_data_source" "security_event_store" {
  data_source_type = "postgres"
  description      = "security_event_store"

  data_source_config = {
    "hostname"                   = var.data_source_security.hostname
    "tlsTerminationOverrideHost" = var.data_source_security.endpoint
    "hostPort"                   = var.data_source_security.port
    "databaseName"               = var.data_source_security.database
    "username"                   = var.data_source_security.username
    "password"                   = var.data_source_security.password
    "publicationName"            = var.data_source_security.publicationName
    "tableName"                  = var.data_source_security.tableName
    "columns"                    = var.data_source_security.columns
    "partitioningColumn"         = var.data_source_security.partitioningColumn
    "serialColumn"               = var.data_source_security.serialColumn
  }
}

resource "ambar_data_source" "credit_card_product" {
  data_source_type = "postgres"
  description      = "credit_card_product_event_store"

  data_source_config = {
    "hostname"                   = var.data_source_credit_card_product.hostname
    "tlsTerminationOverrideHost" = var.data_source_credit_card_product.endpoint
    "hostPort"                   = var.data_source_credit_card_product.port
    "databaseName"               = var.data_source_credit_card_product.database
    "username"                   = var.data_source_credit_card_product.username
    "password"                   = var.data_source_credit_card_product.password
    "publicationName"            = var.data_source_credit_card_product.publicationName
    "tableName"                  = var.data_source_credit_card_product.tableName
    "columns"                    = var.data_source_credit_card_product.columns
    "partitioningColumn"         = var.data_source_credit_card_product.partitioningColumn
    "serialColumn"               = var.data_source_credit_card_product.serialColumn
  }
}