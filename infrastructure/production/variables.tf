variable "application_docker_tag" {
  type = string
  nullable = true
}

variable "pgtproxy_key_in_base64" {
  type = string
  sensitive = true
}

variable "pgtproxy_cert_in_base64" {
  type = string
}