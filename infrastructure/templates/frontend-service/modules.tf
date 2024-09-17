module "networking" {
  source                                           = "./../networking"
  resource_id_prefix                               = "${local.resource_id_prefix_prefix}-net"
  public_cidrs_with_mongo_port_access_to_instances = []
  public_cidrs_with_pg_port_access_to_instances    = []
  public_cidrs_with_ssh_port_access_to_instances   = []
}

module "frontend_application" {
  source                                     = "./../frontend-application"
  resource_id_prefix                         = "${local.resource_id_prefix_prefix}-app"
  application_directory_name                 = var.application_directory_name
  git_commit_hash                            = var.git_commit_hash
  vpc_connector_subnetwork_name              = module.networking.vpc_connector_name
  domain_identity                            = var.domain_identity
  domain_security                            = var.domain_security
}
