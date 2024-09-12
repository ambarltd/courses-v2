resource "random_id" "random_id" {
  byte_length = 10
}

resource "google_compute_address" "database_public_ip" {
  name     = "prdb-${var.environment_name}-${random_id.random_id.hex}"
  region = local.gcp_default_region
}

resource "google_compute_firewall" "allow_http_https_ssh" {
  name    = "allow-${var.environment_name}-${random_id.random_id.hex}"
  network = var.network_name

  allow {
    protocol = "tcp"
    ports    = ["22", "27017", "27018", "27019", "28017"]
  }

  source_ranges = ["0.0.0.0/0"]  # Allowing all traffic, private and public
}

resource "google_compute_disk" "persistent_disk" {
  name    = "disk-${var.environment_name}-${random_id.random_id.hex}"
  type  = "pd-standard"
  size  = 100
  zone  = "${local.gcp_default_region}a"
}

resource "random_password" "admin_user" {
  length      = 16
  min_lower   = 2
  min_upper   = 2
  min_numeric = 2
  special     = false
  min_special = 0
}


resource "google_compute_instance" "vm_instance" {
  name    = "prdb-${var.environment_name}-${random_id.random_id.hex}"
  machine_type = "e2-standard-2"
  zone  = "${local.gcp_default_region}a"

  boot_disk {
    initialize_params {
      image = "debian-cloud/debian-11"
    }
  }

  attached_disk {
    source = google_compute_disk.persistent_disk.id
  }

  network_interface {
    network    = var.network_name
    subnetwork = var.subnetwork_name

    access_config {
      nat_ip = google_compute_address.database_public_ip.address
    }
  }

  metadata = {
    ssh-keys = "root:ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAILZ4BR0V4u/Ch5BH1yscj3xgxmy0L9eRHy1tNL5CPe8q id_ed25519"
  }

  service_account {
    scopes = []
  }

  tags = []

  # Adding a Docker container
  metadata_startup_script = <<-EOT
    #!/bin/bash
    sudo apt-get update
    sudo apt-get install -y docker.io
    sudo docker pull mongo
    sudo docker run -d -p 27017:27017 --env MONGO_INITDB_ROOT_USERNAME=admin_username --env MONGO_INITDB_ROOT_PASSWORD=${random_password.admin_user.result} mongo
  EOT

  allow_stopping_for_update = true

  deletion_protection = false
}
