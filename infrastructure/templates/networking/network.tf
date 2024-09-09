resource "google_compute_network" "main_network" {
  name                    = "network-${var.environment_name}"
  auto_create_subnetworks = false
  routing_mode            = "REGIONAL"
}
