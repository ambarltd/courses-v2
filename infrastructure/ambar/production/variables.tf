variable "data_source_identity" {
  type = object({
    hostname = string
    endpoint = string
    port = string
    database = string
    username = string
    password = string
    publicationName = string
    tableName = string
    columns = string
    partitioningColumn = string
    serialColumn = string
  })
  sensitive = true
}

variable "data_destination_identity" {
  type = object({
    endpoint_prefix: string
  })
}

variable "data_source_security" {
  type = object({
    hostname = string
    endpoint = string
    port = string
    database = string
    username = string
    password = string
    publicationName = string
    tableName = string
    columns = string
    partitioningColumn = string
    serialColumn = string
  })
  sensitive = true
}

variable "data_destination_security" {
  type = object({
    endpoint_prefix : string
  })
}

variable "data_source_credit_card_product" {
  type = object({
    hostname = string
    endpoint = string
    port = string
    database = string
    username = string
    password = string
    publicationName = string
    tableName = string
    columns = string
    partitioningColumn = string
    serialColumn = string
  })
  sensitive = true
}

variable "data_destination_credit_card_product" {
  type = object({
    endpoint_prefix: string
  })
}