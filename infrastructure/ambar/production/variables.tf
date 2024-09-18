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