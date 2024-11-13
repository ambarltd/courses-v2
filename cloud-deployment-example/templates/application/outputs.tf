output "cloudrun_public_https_endpoint" {
  value = google_cloud_run_service.application.status.0.url
}

# Note: Any output that depends on google_cloud_run_service.application
# can be used with the assumption that we already generated databases,
# tables, and users, as specified in the application's startup script.
# Note: This appears not to be the case, it only works like that the first time
# we are executing to create the application.

output "projection_database_name" {
  value = var.mongodb_projection_database_name
  depends_on = [google_cloud_run_service.application]
}

output "reaction_database_name" {
  value = var.mongodb_reaction_database_name
  depends_on = [google_cloud_run_service.application]
}

output "event_store_table_name" {
  value = var.event_store_table_name
  depends_on = [google_cloud_run_service.application]
}

output "event_store_replication_username" {
  value = local.replication_username
  depends_on = [google_cloud_run_service.application]
}

output "event_store_replication_password" {
  value = random_password.replication_password.result
  depends_on = [google_cloud_run_service.application]
}

output "event_store_replication_publication_name" {
  value = local.replication_publication_name
  depends_on = [google_cloud_run_service.application]
}