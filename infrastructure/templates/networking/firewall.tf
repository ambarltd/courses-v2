resource "google_compute_firewall" "inbound_internal" {
  name    = "${var.resource_id_prefix}-int"
  network = google_compute_network.main_network.id

  allow {
     protocol = "tcp"
     ports    = ["1-65535"]
  }

  source_ranges = ["10.0.0.0/16"]
}

resource "google_compute_firewall" "inbound_postgres" {
  name    = "${var.resource_id_prefix}-pg"
  network = google_compute_network.main_network.id

  allow {
     protocol = "tcp"
     ports    = ["5432"]
  }

  source_ranges = var.public_cidrs_with_pg_port_access_to_instances
}

resource "google_compute_firewall" "inbound_ssh" {
  name    = "${var.resource_id_prefix}-ssh"
  network = google_compute_network.main_network.id

  allow {
     protocol = "tcp"
     ports    = ["22"]
  }

  source_ranges = var.public_cidrs_with_ssh_port_access_to_instances
}

resource "google_compute_firewall" "inbound_mongo" {
  name    = "${var.resource_id_prefix}-mon"
  network = google_compute_network.main_network.id

  allow {
    protocol = "tcp"
    ports    = ["27017"]
  }

  source_ranges = var.public_cidrs_with_mongo_port_access_to_instances
}
