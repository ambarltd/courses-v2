variable "data_source_event_store" {
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

variable "data_destination_backend_php" {
  type = object({
    endpoint_prefix: string
  })
}