resource "google_artifact_registry_repository" "docker_repository" {
  location      = local.gcp_default_region
  repository_id = "${var.resource_id_prefix}-pgtre"
  format        = "DOCKER"
}

resource "random_id" "image_tag" {
  byte_length = 8
  keepers = {
    database_ca_cert_hash : md5(var.database_ca_cert_in_base64)
    proxy_key_hash : md5(var.pgtproxy_key_in_base64)
    proxy_cert_hash : md5(var.pgtproxy_cert_in_base64)
    local_ip : var.database_local_network_ip_address
    database_tl_host = data.external.dns_probe.result["database_tls_host"]
    docker_registry_url : local.docker_registry_url
    docker_repository_name : local.docker_repository_name
    dockerfile_hash : filemd5("${path.module}/Dockerfile")
    force_redeploy : "blue" # alternate between blue/green to force redeploy
  }
}

resource "null_resource" "push_image" {
  triggers = {
    "image_tag" : random_id.image_tag.hex
  }
  provisioner "local-exec" {
    # base64 --decode doesn't work in alpine images, needs -d instead
    command     = <<EOT
      set -e
      mkdir -p docker_image_builder_${random_id.image_tag.hex}
      cp Dockerfile docker_image_builder_${random_id.image_tag.hex}/
      cd docker_image_builder_${random_id.image_tag.hex}
      echo ${var.database_ca_cert_in_base64} | base64 -d > trust_this_ca.pem
      echo ${var.pgtproxy_key_in_base64} | base64 -d > tls_private_key.pem
      echo ${var.pgtproxy_cert_in_base64} | base64 -d > tls_certificate.pem
      sed -i 's/CONNECTION_HOST_OR_IP_TO_BE_REPLACED/${var.database_local_network_ip_address}/g' Dockerfile
      sed -i 's/TLS_VALIDATION_HOST_TO_BE_REPLACED/${data.external.dns_probe.result["database_tls_host"]}/g' Dockerfile
      ls -la
      docker build --tag ${local.docker_registry_url}/${local.docker_repository_name}:${random_id.image_tag.hex} .
      echo ${local.gcp_current_access_token_for_docker} | docker login -u oauth2accesstoken --password-stdin https://${local.docker_registry_url}
      docker image push ${local.docker_registry_url}/${local.docker_repository_name}:${random_id.image_tag.hex}
      cd ../
      rm docker_image_builder_${random_id.image_tag.hex} -Rf
    EOT
    working_dir = path.module
  }
}
