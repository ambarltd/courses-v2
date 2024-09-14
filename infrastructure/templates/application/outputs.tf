output "cloudrun_public_https_endpoint" {
  value = google_cloud_run_service.application.status.0.url
}

output "projection_database_name" {
  value = var.mongodb_projection_database_name
}

output "reaction_database_name" {
  value = var.mongodb_reaction_database_name
}