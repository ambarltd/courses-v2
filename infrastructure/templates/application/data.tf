data "google_client_config" "this" {}

locals {
  gcp_default_region = data.google_client_config.this.region
}