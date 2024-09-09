output "database_private_ip_address" {
  value = google_sql_database_instance.main.private_ip_address
}

# TLS cert uses this as common name for PG connections
output "database_connection_name" {
  value = google_sql_database_instance.main.connection_name
}

output "database_ca_cert_in_base64" {
  value = base64encode(google_sql_database_instance.main.server_ca_cert.0.cert)
}