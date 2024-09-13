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

output "database_name" {
  value = google_sql_database.main.name
}

output "database_admin_username" {
  value = google_sql_user.admin_user.name
}

output "database_admin_password" {
  value = random_password.admin_user.result
}

output "database_port" {
  value = 5432
}