variable "resource_id_prefix" {
  type = string
}

variable "git_commit_hash" {
  type = string
}

variable "application_directory_name" {
  type = string
}

variable "vpc_connector_subnetwork_name" {
  type = string
}

variable "event_store_host" {
  type = string
}

variable "event_store_port" {
  type = number
}

variable "event_store_database_name" {
  type = string
}

variable "event_store_table_name" {
  type = string
}

variable "event_store_user" {
  type = string
}

variable "event_store_password" {
  type = string
}

variable "mongodb_projection_host" {
  type = string
}

variable "mongodb_projection_port" {
  type = number
}

variable "mongodb_projection_authentication_database" {
  type = string
}

variable "mongodb_projection_database_name" {
  type = string
}

variable "mongodb_projection_database_username" {
  type = string
}

variable "mongodb_projection_database_password" {
  type = string
}

variable "mongodb_reaction_host" {
  type = string
}

variable "mongodb_reaction_port" {
  type = number
}

variable "mongodb_reaction_authentication_database" {
  type = string
}

variable "mongodb_reaction_database_name" {
  type = string
}

variable "mongodb_reaction_database_username" {
  type = string
}

variable "mongodb_reaction_database_password" {
  type = string
}

variable "session_tokens_expire_after_seconds" {
  type = number
}

variable "full_service_name_in_lowercase" {
  type = string
}