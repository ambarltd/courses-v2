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
          name  = "EVENT_STORE_HOST"
          value = var.event_store_host
        }
        env {
          name  = "EVENT_STORE_PORT"
          value = var.event_store_port
        }
        env {
          name  = "EVENT_STORE_DATABASE_NAME"
          value = var.event_store_database_name
        }
        env {
          name  = "EVENT_STORE_USER"
          value = var.event_store_user
        }
        env {
          name  = "EVENT_STORE_PASSWORD"
          value = var.event_store_password
        }
        env {
          name  = "EVENT_STORE_CREATE_TABLE_WITH_NAME"
          value = var.event_store_table_name
        }
        env {
          name  = "EVENT_STORE_CREATE_REPLICATION_USER_WITH_USERNAME"
          value = local.replication_username
        }
        env {
          name  = "EVENT_STORE_CREATE_REPLICATION_USER_WITH_PASSWORD"
          value = random_password.replication_password.result
        }
        env {
          name  = "EVENT_STORE_CREATE_REPLICATION_PUBLICATION"
          value = local.replication_publication_name
        }
        env {
          name  = "MONGODB_PROJECTION_HOST"
          value = var.mongodb_projection_host
        }
        env {
          name  = "MONGODB_PROJECTION_PORT"
          value = var.mongodb_projection_port
        }
        env {
          name  = "MONGODB_PROJECTION_AUTHENTICATION_DATABASE"
          value = var.mongodb_projection_authentication_database
        }
        env {
          name  = "MONGODB_PROJECTION_DATABASE_NAME"
          value = var.mongodb_projection_database_name
        }
        env {
          name  = "MONGODB_PROJECTION_DATABASE_USERNAME"
          value = var.mongodb_projection_database_username
        }
        env {
          name  = "MONGODB_PROJECTION_DATABASE_PASSWORD"
          value = var.mongodb_projection_database_password
        }
        env {
          name  = "MONGODB_REACTION_HOST"
          value = var.mongodb_reaction_host
        }
        env {
          name  = "MONGODB_REACTION_PORT"
          value = var.mongodb_reaction_port
        }
        env {
          name  = "MONGODB_REACTION_AUTHENTICATION_DATABASE"
          value = var.mongodb_reaction_authentication_database
        }
        env {
          name  = "MONGODB_REACTION_DATABASE_NAME"
          value = var.mongodb_reaction_database_name
        }
        env {
          name  = "MONGODB_REACTION_DATABASE_USERNAME"
          value = var.mongodb_reaction_database_username
        }
        env {
          name  = "MONGODB_REACTION_DATABASE_PASSWORD"
          value = var.mongodb_reaction_database_password
        }
        env {
          name  = "SESSION_TOKENS_EXPIRE_AFTER_SECONDS"
          value = var.session_tokens_expire_after_seconds
        }
      }
    }
    metadata {
      annotations = {
        "run.googleapis.com/vpc-access-connector" = var.vpc_connector_subnetwork_name
        "autoscaling.knative.dev/minScale" = 1
      }
    }
  }

  traffic {
    percent         = 100
    latest_revision = true
  }

  depends_on = [null_resource.push_app_image]
}

locals {
  replication_username = "ambar_user"
  replication_publication_name = "ambar_publication"
}

resource "random_password" "replication_password" {
  length  = 15
  special = false
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
