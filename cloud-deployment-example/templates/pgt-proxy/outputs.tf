output "public_ip" {
  value = google_compute_instance.postgres_proxy.network_interface.0.access_config.0.nat_ip
}