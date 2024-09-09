resource "google_compute_subnetwork" "subnet1" {
  name          = "subnet1-${var.environment_name}"
  ip_cidr_range = "10.0.1.0/24"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}

resource "google_compute_subnetwork" "subnet2" {
  name          = "subnet2-${var.environment_name}"
  ip_cidr_range = "10.0.2.0/24"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}

resource "google_compute_subnetwork" "subnet3" {
  name          = "subnet3-${var.environment_name}"
  ip_cidr_range = "10.0.3.0/24"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}
