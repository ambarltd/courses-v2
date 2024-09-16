resource "google_cloud_run_service" "application" {
  name     = "${var.resource_id_prefix}-app"
  location = local.gcp_default_region

  template {
    spec {
      containers {
        image = local.docker_full_address
        ports {
          container_port = 8080
        }
      }
    }
    metadata {
      annotations = {
        "run.googleapis.com/vpc-access-connector" = var.vpc_connector_subnetwork_name
      }
    }
  }

  traffic {
    percent         = 100
    latest_revision = true
  }

  depends_on = [null_resource.push_app_image]
}

resource "google_cloud_run_service_iam_policy" "public_policy" {
  location = google_cloud_run_service.application.location
  project  = google_cloud_run_service.application.project
  service  = google_cloud_run_service.application.name

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
