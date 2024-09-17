terraform {
  required_providers {
    ambar = {
      source  = "ambarltd/ambar"
      version = "1.0.10"
    }
    google = {
      source  = "hashicorp/google"
      version = "6.2.0"
    }
  }

  backend "s3" {
    encrypt = true
  }
}

provider "ambar" {
  api_key = base64decode(local.credentials["ambar_api_key"])
  endpoint   = "euw1.api.ambar.cloud"
  alias      = "production"
}

provider "google" {
  credentials = base64decode(local.credentials["gcp_service_account_json_in_base64"])
  project     = "drew-production"
  region      = "europe-west2"
  alias       = "production"
}
