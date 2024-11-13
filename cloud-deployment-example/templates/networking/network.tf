resource "google_compute_network" "main_network" {
  name                    = "${var.resource_id_prefix}-nw"
  auto_create_subnetworks = false
  routing_mode            = "REGIONAL"
}
