resource "google_compute_subnetwork" "subnet1" {
  name          = "${var.resource_id_prefix}-sn1"
  ip_cidr_range = "10.0.1.0/24"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}

resource "google_compute_subnetwork" "subnet2" {
  name          = "${var.resource_id_prefix}-sn2"
  ip_cidr_range = "10.0.2.0/24"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}

resource "google_compute_subnetwork" "subnet3" {
  name          = "${var.resource_id_prefix}-sn3"
  ip_cidr_range = "10.0.3.0/24"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}

resource "google_compute_subnetwork" "vpc_connector_subnetwork" {
  name          = "${var.resource_id_prefix}-snvc"
  ip_cidr_range = "10.0.196.0/28"
  region        = local.gcp_default_region
  network       = google_compute_network.main_network.id
}
