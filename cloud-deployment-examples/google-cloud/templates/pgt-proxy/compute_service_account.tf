resource "google_service_account" "pgt_service_acc" {
  account_id   = "${var.resource_id_prefix}-acc"
  display_name = "${var.resource_id_prefix}-acc"
}

resource "google_project_iam_member" "pgt_art_read" {
  role    = "roles/artifactregistry.reader"
  member  = "serviceAccount:${google_service_account.pgt_service_acc.email}"
  project = local.gcp_default_project
}

resource "google_project_iam_member" "pgt_log_write" {
  role    = "roles/logging.logWriter"
  member  = "serviceAccount:${google_service_account.pgt_service_acc.email}"
  project = local.gcp_default_project
}