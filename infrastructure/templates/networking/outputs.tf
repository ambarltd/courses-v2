output "network_id" {
  value = google_compute_network.main_network.id

  depends_on = [google_service_networking_connection.private_vpc_connection]
}

output "network_name" {
  value = google_compute_network.main_network.id

  depends_on = [google_service_networking_connection.private_vpc_connection]
}

output "network_region" {
  value = local.gcp_default_region

  depends_on = [google_service_networking_connection.private_vpc_connection]
}

output "subnet_2_cidr_range" {
  value = google_compute_subnetwork.subnet2.ip_cidr_range
}

output "subnetwork_id_1" {
  value = google_compute_subnetwork.subnet1.id

  depends_on = [google_service_networking_connection.private_vpc_connection]
}

output "subnetwork_id_2" {
  value = google_compute_subnetwork.subnet1.id

  depends_on = [google_service_networking_connection.private_vpc_connection]
}

output "subnetwork_id_3" {
  value = google_compute_subnetwork.subnet1.id

  depends_on = [google_service_networking_connection.private_vpc_connection]
}