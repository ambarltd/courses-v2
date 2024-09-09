resource "google_spanner_instance" "main" {
  config       = "regional-${local.gcp_default_region}"
  display_name = "main-instance"
  num_nodes    = 1
}

resource "google_spanner_database" "database" {
  instance = google_spanner_instance.main.name
  name     = "projection-${var.environment_name}"
  version_retention_period = "3d"
  ddl = []
  deletion_protection = false
}