# necessary for instances in the network to access google services such as cloud sql
resource "google_compute_global_address" "peering" {
  name          = "${var.resource_id_prefix}-prn"
  purpose       = "VPC_PEERING"
  address_type  = "INTERNAL"
  prefix_length = 16
  network       = google_compute_network.main_network.id
}

resource "google_service_networking_connection" "private_vpc_connection" {
  network                 = google_compute_network.main_network.id
  service                 = "servicenetworking.googleapis.com"
  reserved_peering_ranges = [google_compute_global_address.peering.name]
  deletion_policy         = "ABANDON"
}