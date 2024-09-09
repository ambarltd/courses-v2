module "production" {
  source = "./production"

  application_docker_tag = null
  providers = {
    google = google.production
  }
  pgtproxy_cert_in_base64 = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64  = local.credentials["pgtproxy_key_in_base64"]
}