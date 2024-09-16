output "connection_outputs" {
  value = {
    application_domain                         = module.frontend_application.cloudrun_public_https_endpoint
  }
}
