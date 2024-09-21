resource "google_compute_instance" "postgres_proxy" {
  # Forces recreation on new image (gcp docker doesn't actually deploy new image)
  name                      = "${var.resource_id_prefix}-${random_id.image_tag.hex}-ins"
  machine_type              = "e2-micro"
  zone                      = "${local.gcp_default_region}-a"
  allow_stopping_for_update = true

  boot_disk {
    initialize_params {
      image = "cos-cloud/cos-stable"
    }
  }

  network_interface {
    network    = var.network_id_with_destination_database
    subnetwork = var.subnetwork_id
    access_config {
      nat_ip = google_compute_address.pgt_proxy_ip.address
    }
  }

  service_account {
    email = google_service_account.pgt_service_acc.email
    scopes = [
      "https://www.googleapis.com/auth/cloud-platform",
      "https://www.googleapis.com/auth/devstorage.read_only",
      "https://www.googleapis.com/auth/logging.write",
      "https://www.googleapis.com/auth/monitoring.write",
      "https://www.googleapis.com/auth/servicecontrol",
      "https://www.googleapis.com/auth/service.management.readonly",
      "https://www.googleapis.com/auth/trace.append"
    ]
  }

  metadata = {
    gce-container-declaration = <<-EOT
spec:
  containers:
    - name: proxy
      image: '${local.docker_registry_url}/${local.docker_repository_name}:${random_id.image_tag.hex}'
      stdin: true
      tty: true
      ports:
        - hostPort: 5432
          containerPort: 5432
  restartPolicy: Always
EOT
    google-logging-enabled    = "true"
    google-monitoring-enabled = "true"
  }

  depends_on = [google_artifact_registry_repository.docker_repository, null_resource.push_image]

  tags = ["pgtproxy", "inboundssh"]
}


