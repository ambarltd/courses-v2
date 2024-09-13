locals {
  gcp_default_region = data.google_client_config.this.region
  gcp_default_project= data.google_client_config.this.project
}