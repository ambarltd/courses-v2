resource "google_compute_address" "pgt_proxy_ip" {
  name = "pgt-proxy-ip-${var.environment_name}"
}