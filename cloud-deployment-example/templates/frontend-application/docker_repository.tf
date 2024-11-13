resource "google_artifact_registry_repository" "docker_repository" {
  location      = local.gcp_default_region
  repository_id = "${var.resource_id_prefix}-rep"
  format        = "DOCKER"
}
