output "cloudrun_public_https_endpoint" {
  value = google_cloud_run_service.application.status.0.url
}