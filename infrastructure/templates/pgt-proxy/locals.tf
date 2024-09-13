locals {
  docker_registry_url = "${google_artifact_registry_repository.docker_repository.location}-docker.pkg.dev"
  docker_repository_name = "${google_artifact_registry_repository.docker_repository.project}/${google_artifact_registry_repository.docker_repository.name}/pgt-proxy"
  gcp_default_region = data.google_client_config.this.region
  gcp_default_project = data.google_client_config.this.project
  gcp_current_access_token_for_docker = data.google_client_config.this.access_token
  dns_probe_filename = "dns_host_${random_id.dns_probe.hex}.txt"
  dns_probe_file_location = "${path.module}/dns_host_${random_id.dns_probe.hex}.txt"
}