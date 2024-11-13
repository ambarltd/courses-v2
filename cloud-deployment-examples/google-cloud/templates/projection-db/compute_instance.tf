resource "google_compute_instance" "vm_instance" {
  # doesn't seem to work
  name         = local.mongo_instance_name
  machine_type = "e2-micro"
  zone         = "${local.gcp_default_region}-a"

  boot_disk {
    initialize_params {
      image = "debian-cloud/debian-11"
    }
  }

  attached_disk {
    source = google_compute_disk.persistent_disk.id
    device_name = "mydisk"
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

  metadata_startup_script = local.mongo_startup_script

  allow_stopping_for_update = true

  deletion_protection = false
}

locals {
  # The disk stays stable, but if we change the startup script, we want to make sure that we restart the clock
  # on the setup wait time, so we'll create a new instance altogether, which will trigger the
  # time_sleep.
  mongo_instance_name = "${var.resource_id_prefix}-prdb-${substr(md5(local.mongo_startup_script), 0, 6)}"
  mongo_startup_script = <<-EOT
    #!/bin/bash
    sudo apt-get update
    sudo apt-get install -y docker.io

    # Create a mount point for the attached disk
    sudo mkdir -p /mnt/disks/data-disk

    # Check if the disk is already formatted, if not format it
    if ! blkid /dev/disk/by-id/google-mydisk; then
      sudo mkfs.ext4 /dev/disk/by-id/google-mydisk
    fi

    # Mount the disk
    sudo mount /dev/disk/by-id/google-mydisk /mnt/disks/data-disk

    # Ensure the disk will be remounted on reboot
    echo "/dev/disk/by-id/google-mydisk /mnt/disks/data-disk ext4 defaults 0 0" | sudo tee -a /etc/fstab

    # Create a subdirectory for MongoDB data
    sudo mkdir -p /mnt/disks/data-disk/mongodb-data
    sudo chmod 777 /mnt/disks/data-disk/mongodb-data

    # Pull and run the MongoDB container with volume mapping
    sudo docker pull mongo
    sudo docker run -d -p 27017:27017 \
      --env MONGO_INITDB_ROOT_USERNAME=admin_username \
      --env MONGO_INITDB_ROOT_PASSWORD=${random_password.admin_user.result} \
      -v /mnt/disks/data-disk/mongodb-data:/data/db mongo
  EOT
}

resource "time_sleep" "wait_for_database_setup" {
  create_duration = "600s" # Wait 10 minutes to be sure mongo has downloaded, and not have superfluous failed builds
  depends_on = [google_compute_instance.vm_instance, google_compute_disk.persistent_disk]

  triggers = {
    instance_name = google_compute_instance.vm_instance.name
    instance_recreation = google_compute_instance.vm_instance.network_interface[0].network_ip
  }
}
