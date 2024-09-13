terraform {
  required_providers {
    ambar = {
      source  = "ambarltd/ambar"
      version = "1.0.6"
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

provider "google" {
  credentials = base64decode(local.credentials["gcp_service_account_json_in_base64"])
  project     = "drew-production"
  region      = "europe-west2"
  alias       = "production"
}
