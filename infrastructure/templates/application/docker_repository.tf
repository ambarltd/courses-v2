resource "google_artifact_registry_repository" "docker_repository" {
  location      = local.gcp_default_region
  repository_id = "application-repository-${var.environment_name}-${var.application_directory_name}"
  format        = "DOCKER"
}
