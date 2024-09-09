locals {
  docker_repository_address = "${google_artifact_registry_repository.docker_repository.location}-docker.pkg.dev/${google_artifact_registry_repository.docker_repository.project}/${google_artifact_registry_repository.docker_repository.name}"
}