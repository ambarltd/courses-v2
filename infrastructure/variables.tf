variable "credentials_base64" {
  type      = string
  sensitive = true
}

variable "git_commit_hash" {
  type = string
}

locals {
  credentials = jsondecode(base64decode(var.credentials_base64))
}