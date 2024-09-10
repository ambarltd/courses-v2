resource "random_id" "dns_probe" {
  byte_length = 16
  keepers = {
      force_redeploy: "green" # alternate between blue/green to force redeploy
  }
}

resource "tls_private_key" "dns_probe" {
  algorithm = "RSA"
  rsa_bits  = 4096
}

resource "google_compute_instance" "dns_probe" {
  name         = "pgt-probe--${var.environment_name}-${random_id.dns_probe.hex}"
  machine_type = "e2-micro"
  zone = "${local.gcp_default_region}-a"

  boot_disk {
    initialize_params {
      image = "ubuntu-os-cloud/ubuntu-2404-lts-amd64"
    }
  }

  network_interface {
    network = var.network_id_with_destination_database
    subnetwork = var.subnetwork_id
    access_config {
      // empty block -> gets random public ip
    }
  }

  metadata = {
    ssh-keys = "probe_user:${tls_private_key.dns_probe.public_key_openssh}"
  }

  tags = ["inboundssh"]

  lifecycle {
    ignore_changes = [
      boot_disk[0].initialize_params[0].image
    ]
  }
}

resource "time_sleep" "dns_probe_wait_60_seconds" {
  depends_on = [google_compute_instance.dns_probe]

  # Needed for instance to come alive, and allow for user login
  create_duration = "60s"
}

data "external" "dns_probe" {
  program = ["bash", "docker_dns_probe.sh"]

  query = {
    private_key_pem_in_base64 = base64encode(tls_private_key.dns_probe.private_key_pem)
    dns_probe_ssh_key_filename = "dns_probe_key_${random_id.dns_probe.hex}.pem"
    dns_probe_instance_public_ip = google_compute_instance.dns_probe.network_interface.0.access_config.0.nat_ip
    database_local_network_ip_address = var.database_local_network_ip_address
    dns_probe_resolved_filename = "dns_probe_resolved_filename_${random_id.dns_probe.hex}.resolved"
  }

  working_dir = path.module

  depends_on = [time_sleep.dns_probe_wait_60_seconds]
}


