variable "resource_id_prefix" {
  type = string
}

variable "public_cidrs_with_access" {
  type = list(string)
}

variable "network_id_with_private_access" {
  type = string
}