resource "random_id" "vpc_connector_id" {
  byte_length = 8
}

resource "google_vpc_access_connector" "vpc_connector_for_cloudrun" {
  name        = lower(random_id.vpc_connector_id.hex)
  subnet {
    name = google_compute_subnetwork.vpc_connector_subnetwork.name
  }
  min_instances = 2
  max_instances = 3
}