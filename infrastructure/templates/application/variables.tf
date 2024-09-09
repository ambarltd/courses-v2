variable "environment_name" {
  type = string
}

variable "application_docker_tag" {
  type = string
  nullable = true
  description = "Deploy a CloudRun service with this docker tag. If no tag is specified, no service is deployed."
}