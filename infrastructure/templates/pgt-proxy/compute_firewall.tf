resource "google_compute_firewall" "pgt_proxy" {
  name    = "pgt-proxy-firewall-${var.environment_name}"
  network = var.network_id_with_destination_database

  allow {
     protocol = "tcp"
     ports    = ["5432"]
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags = ["pgtproxy"]
}

resource "google_compute_firewall" "inbound_ssh" {
  name    = "inbound-ssh-${var.environment_name}"
  network = var.network_id_with_destination_database

  allow {
     protocol = "tcp"
     ports    = ["22"]
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags = ["inboundssh"]
}
