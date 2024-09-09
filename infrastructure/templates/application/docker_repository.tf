resource "google_artifact_registry_repository" "docker_repository" {
  location      = local.gcp_default_region
  repository_id = "application-repository-${var.environment_name}"
  format        = "DOCKER"
}

resource "random_password" "docker_repository" {
  length      = 16
  min_lower   = 2
  min_upper   = 2
  min_numeric = 2
  special     = false
  min_special = 0
}
