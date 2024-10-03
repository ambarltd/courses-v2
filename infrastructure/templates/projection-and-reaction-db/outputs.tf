output "database_public_ip" {
  value = google_compute_address.database_public_ip.address

  depends_on = [time_sleep.wait_for_database_setup]
}

output "database_private_ip" {
  value = google_compute_instance.vm_instance.network_interface[0].network_ip

  depends_on = [time_sleep.wait_for_database_setup]
}

output "authentication_database" {
  value = "admin"
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