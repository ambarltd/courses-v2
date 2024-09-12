output "database_public_up" {
  value = google_compute_address.database_public_ip.address
}

output "admin_password" {
  value = random_password.admin_user.result
}