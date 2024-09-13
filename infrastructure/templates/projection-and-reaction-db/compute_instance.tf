resource "google_compute_instance" "vm_instance" {
  name    = "${var.resource_id_prefix}-prdb"
  machine_type = "e2-standard-2"
  zone  = "${local.gcp_default_region}-a"

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
