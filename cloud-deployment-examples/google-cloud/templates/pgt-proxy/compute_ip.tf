resource "google_compute_address" "pgt_proxy_ip" {
  name = "${var.resource_id_prefix}-ip"
}