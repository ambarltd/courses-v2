output "database_public_ip" {
  value = google_compute_address.database_public_ip.address
}

output "database_port" {
  value = 27017
}

output "admin_username" {
  value = "admin_username"
}

output "admin_password" {
  value = random_password.admin_user.result
}