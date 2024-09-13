resource "google_compute_address" "database_public_ip" {
  name     = "${var.resource_id_prefix}-dbip"
  region = local.gcp_default_region
}

resource "google_compute_disk" "persistent_disk" {
  name    = "${var.resource_id_prefix}-dsk"
  type  = "pd-standard"
  size  = 100
  zone  = "${local.gcp_default_region}-a"
}