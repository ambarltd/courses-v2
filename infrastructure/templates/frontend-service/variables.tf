variable "environment_uuid" {
  type        = string
  description = "Must be unique, across all deployments in the universe."
  validation {
    condition     = length(var.environment_uuid) == 17
    error_message = "Environment uuid must be 17 characters (short enough so resources pass validation)"
  }
}

variable "service_name" {
  type = string
  validation {
    condition     = length(var.service_name) == 3
    error_message = "Service name must be 3 characters (short enough so resources pass validation)"
  }
}

variable "git_commit_hash" {
  type = string
}

variable "application_directory_name" {
  type = string
}

variable "domain_identity" {
  type = string
}

variable "domain_security" {
  type = string
}