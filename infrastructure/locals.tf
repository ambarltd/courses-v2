locals {
  credentials = jsondecode(base64decode(var.credentials_base64))
}