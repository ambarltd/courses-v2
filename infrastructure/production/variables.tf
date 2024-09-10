variable "pgtproxy_key_in_base64" {
  type = string
  sensitive = true
}

variable "pgtproxy_cert_in_base64" {
  type = string
}

variable "git_commit_hash" {
  type = string
}