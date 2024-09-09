resource "google_cloud_run_service" "application" {
  count = var.application_docker_tag != null ? 1 : 0
  name     = "application-${var.environment_name}"
  location = local.gcp_default_region

  template {
    spec {
      containers {
        image = "${local.docker_repository_address}/${var.application_docker_tag}"
        ports {
          container_port = 80
        }
      }
    }
  }

  traffic {
    percent         = 100
    latest_revision = true
  }
}

resource "google_cloud_run_service_iam_policy" "public_policy" {
  count = var.application_docker_tag != null ? 1 : 0
  location = google_cloud_run_service.application[0].location
  project  = google_cloud_run_service.application[0].project
  service  = google_cloud_run_service.application[0].name

  policy_data = <<EOF
{
  "bindings": [
    {
      "role": "roles/run.invoker",
      "members": [
        "allUsers"
      ]
    }
  ]
}
EOF
}
