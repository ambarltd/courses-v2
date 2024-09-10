module "production" {
  source = "./production"

  providers = {
    google = google.production
  }
  pgtproxy_cert_in_base64 = local.credentials["pgtproxy_cert_in_base64"]
  pgtproxy_key_in_base64  = local.credentials["pgtproxy_key_in_base64"]
  git_commit_hash         = var.git_commit_hash
}