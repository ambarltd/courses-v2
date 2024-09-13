variable "resource_id_prefix" {
  type = string
}

variable "pgtproxy_key_in_base64" {
  type = string
  sensitive = true
}

variable "pgtproxy_cert_in_base64" {
  type = string
}

variable "network_id_with_destination_database" {
  type = string
}

variable "subnetwork_id" {
  type = string
}

variable "database_local_network_ip_address" {
  type = string
}

variable "database_ca_cert_in_base64" {
  type = string
}