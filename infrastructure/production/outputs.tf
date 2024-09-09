output "all_outputs" {
  value = {
    application_docker_repository_address: module.application.docker_repository_address
    pgt_proxy_ip: module.pgt_proxy.public_ip
  }
}