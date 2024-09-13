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
        env {
          name = "API_DOMAIN"
          value = var.api_domain
        }
        env {
          name = "EVENT_STORE_HOST"
          value = var.event_store_host
        }
        env {
          name = "EVENT_STORE_PORT"
          value = var.event_store_port
        }
        env {
          name = "EVENT_STORE_DATABASE_NAME"
          value = var.event_store_database_name
        }
        env {
          name = "EVENT_STORE_USER"
          value = var.event_store_user
        }
        env {
          name = "EVENT_STORE_PASSWORD"
          value = var.event_store_password
        }
        env {
          name = "MONGODB_PROJECTION_HOST"
          value = var.mongodb_projection_host
        }
        env {
          name = "MONGODB_PROJECTION_PORT"
          value = var.mongodb_projection_port
        }
        env {
          name = "MONGODB_PROJECTION_AUTHENTICATION_DATABASE"
          value = var.mongodb_projection_authentication_database
        }
        env {
          name = "MONGODB_PROJECTION_DATABASE_NAME"
          value = var.mongodb_projection_database_name
        }
        env {
          name = "MONGODB_PROJECTION_DATABASE_USERNAME"
          value = var.mongodb_projection_database_username
        }
        env {
          name = "MONGODB_PROJECTION_DATABASE_PASSWORD"
          value = var.mongodb_projection_database_password
        }
        env {
          name = "MONGODB_REACTION_HOST"
          value = var.mongodb_reaction_host
        }
        env {
          name = "MONGODB_REACTION_PORT"
          value = var.mongodb_reaction_port
        }
        env {
          name = "MONGODB_REACTION_AUTHENTICATION_DATABASE"
          value = var.mongodb_reaction_authentication_database
        }
        env {
          name = "MONGODB_REACTION_DATABASE_NAME"
          value = var.mongodb_reaction_database_name
        }
        env {
          name = "MONGODB_REACTION_DATABASE_USERNAME"
          value = var.mongodb_reaction_database_username
        }
        env {
          name = "MONGODB_REACTION_DATABASE_PASSWORD"
          value = var.mongodb_reaction_database_password
        }
        env {
          name = "SESSION_TOKENS_EXPIRE_AFTER_SECONDS"
          value = var.session_tokens_expire_after_seconds
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
