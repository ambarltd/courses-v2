resource "google_compute_address" "database_public_ip" {
  name     = "${var.resource_id_prefix}-dbip"
  region = local.gcp_default_region
}

resource "google_compute_firewall" "allow_inb" {
  name    = "${var.resource_id_prefix}-inb"
  network = var.network_name

  allow {
    protocol = "tcp"
    ports    = ["22", "27017", "27018", "27019", "28017"]
  }

  source_ranges = ["0.0.0.0/0"]  # Allowing all traffic, private and public
}

resource "google_compute_disk" "persistent_disk" {
  name    = "${var.resource_id_prefix}-dsk"
  type  = "pd-standard"
  size  = 100
  zone  = "${local.gcp_default_region}-a"
}