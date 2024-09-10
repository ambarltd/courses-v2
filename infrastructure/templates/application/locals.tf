locals {
  docker_registry_url = "${google_artifact_registry_repository.docker_repository.location}-docker.pkg.dev"
  docker_repository_name = "${google_artifact_registry_repository.docker_repository.project}/${google_artifact_registry_repository.docker_repository.name}/application"
  docker_full_address = "${google_artifact_registry_repository.docker_repository.location}-docker.pkg.dev/${google_artifact_registry_repository.docker_repository.project}/${google_artifact_registry_repository.docker_repository.name}/application:${random_id.image_tag.hex}"
  gcp_default_region = data.google_client_config.this.region
  gcp_default_project = data.google_client_config.this.project
  gcp_current_access_token_for_docker = data.google_client_config.this.access_token
}