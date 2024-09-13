resource "google_vpc_access_connector" "vpc_connector_for_cloudrun" {
  name        = "${var.resource_id_prefix}"
  subnet {
    name = google_compute_subnetwork.vpc_connector_subnetwork.name
  }
  min_instances = 2
  max_instances = 3
}