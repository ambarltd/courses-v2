resource "google_sql_user" "admin_user" {
  project  = local.gcp_default_project
  name     = "admin_user"
  instance = google_sql_database_instance.main.name
  password = random_password.admin_user.result

  // this gets blocked from deleting, unless we block all associated objects
  // thus just delete the cloud sql instance itself when you need to delete it, and ignore this resource
  lifecycle {
    prevent_destroy = true
  }
}

resource "random_password" "admin_user" {
  length      = 16
  min_lower   = 2
  min_upper   = 2
  min_numeric = 2
  special     = false
  min_special = 0
}
