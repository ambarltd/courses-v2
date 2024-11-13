variable "resource_id_prefix" {
  type = string
}

variable "public_cidrs_with_pg_port_access_to_instances" {
  type = list(string)
}

variable "public_cidrs_with_ssh_port_access_to_instances" {
  type = list(string)
}

variable "public_cidrs_with_mongo_port_access_to_instances" {
  type = list(string)
}