resource "random_id" "image_tag" {
  byte_length = 16
  keepers = {
    git_commit_hash : var.git_commit_hash # redeploy with any new git commit
    force_redeploy : "green"              # alternate between blue/green to force redeploy
  }
}

resource "null_resource" "push_app_image" {
  triggers = {
    "image_tag" : random_id.image_tag.hex
  }
  provisioner "local-exec" {
    # base64 --decode doesn't work in alpine images, needs -d instead
    command     = <<EOT
      set -e
      mkdir -p docker_image_builder_${var.application_directory_name}_${random_id.image_tag.hex}
      cp /var/application/${var.application_directory_name} docker_image_builder_${var.application_directory_name}_${random_id.image_tag.hex}/ -Rf
      cd docker_image_builder_${var.application_directory_name}_${random_id.image_tag.hex}/${var.application_directory_name}
      ls -la
      docker build --build-arg SERVICE_NAME_IN_LOWERCASE=${var.full_service_name_in_lowercase} --tag ${local.docker_registry_url}/${local.docker_repository_name}:${random_id.image_tag.hex} .
      echo ${local.gcp_current_access_token_for_docker} | docker login -u oauth2accesstoken --password-stdin https://${local.docker_registry_url}
      docker image push ${local.docker_registry_url}/${local.docker_repository_name}:${random_id.image_tag.hex}
      cd ../../
      rm docker_image_builder_${var.application_directory_name}_${random_id.image_tag.hex}/ -Rf
    EOT
    working_dir = path.module
  }
}
