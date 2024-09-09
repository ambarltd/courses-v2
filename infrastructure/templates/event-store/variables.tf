variable "environment_name" {
  type = string
}

variable "public_cidr_ranges_with_access" {
  type = list(string)
}

variable "network_id_with_private_access" {
  type = string
}