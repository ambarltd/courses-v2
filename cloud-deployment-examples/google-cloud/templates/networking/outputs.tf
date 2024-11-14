output "network_name" {
  value = google_compute_network.main_network.name

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}

output "network_id" {
  value = google_compute_network.main_network.id

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}

output "subnetwork1_name" {
  value = google_compute_subnetwork.subnet1.name

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}

output "subnetwork_id_1" {
  value = google_compute_subnetwork.subnet1.id

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}

output "subnetwork_id_2" {
  value = google_compute_subnetwork.subnet1.id

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}

output "subnetwork_id_3" {
  value = google_compute_subnetwork.subnet1.id

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}

output "vpc_connector_name" {
  value = google_vpc_access_connector.vpc_connector_for_cloudrun.name

  depends_on = [google_service_networking_connection.private_vpc_connection, google_vpc_access_connector.vpc_connector_for_cloudrun]
}
