resource "google_sql_database" "main" {
  project   = local.gcp_default_project
  name      = "${var.resource_id_prefix}-db"
  instance  = google_sql_database_instance.main.name
  charset   = "UTF8"
  collation = "en_US.UTF8"
}


resource "google_sql_database_instance" "main" {
  project             = local.gcp_default_project
  name                = "${var.resource_id_prefix}-i1"
  database_version    = "POSTGRES_15"
  region              = local.gcp_default_region
  deletion_protection = false

  settings {
    # i.e., 1cpu, 4gb ram
    tier              = "db-custom-1-4096"
    edition           = "ENTERPRISE"
    activation_policy = "ALWAYS"
    availability_type = "ZONAL"

    backup_configuration {
      enabled                        = true
      start_time                     = "02:55"
      point_in_time_recovery_enabled = true
      transaction_log_retention_days = 7
      backup_retention_settings {
        retained_backups = 40
        retention_unit   = "COUNT"
      }
    }

    ip_configuration {
      ipv4_enabled                                  = true
      private_network                               = var.network_id_with_private_access
      ssl_mode                                      = "ENCRYPTED_ONLY"
      enable_private_path_for_google_cloud_services = true

      dynamic "authorized_networks" {
        for_each = var.public_cidrs_with_access
        iterator = cidr

        content {
          name  = "cidr-${cidr.key}"
          value = cidr.value
        }
      }
    }

    location_preference {
      zone = "${local.gcp_default_region}-a"
    }

    database_flags {
      name  = "cloudsql.enable_pglogical"
      value = "off"
    }

    database_flags {
      name  = "cloudsql.logical_decoding"
      value = "on"
    }

    maintenance_window {
      day          = 6
      hour         = 3
      update_track = "stable"
    }
  }
}
