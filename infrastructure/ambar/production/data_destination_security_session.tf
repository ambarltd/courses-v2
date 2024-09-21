resource "ambar_data_destination" "Security_Session_HashedPassword" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Security_Session_HashedPassword"
  destination_endpoint = "${var.data_destination_security.endpoint_prefix}/api/v1/security/session/projection/hashed_password"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Security_Session_Session" {
  filter_ids = [
    ambar_filter.security_all.resource_id,
  ]
  description          = "Security_Session_Session"
  destination_endpoint = "${var.data_destination_security.endpoint_prefix}/api/v1/security/session/projection/session"
  username             = "username"
  password             = "password"
}


resource "ambar_data_destination" "Security_Session_UserWithEmail" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Security_Session_UserWithEmail"
  destination_endpoint = "${var.data_destination_security.endpoint_prefix}/api/v1/security/session/projection/user_with_email"
  username             = "username"
  password             = "password"
}


resource "ambar_data_destination" "Security_Session_UserWithUsername" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Security_Session_UserWithUsername"
  destination_endpoint = "${var.data_destination_security.endpoint_prefix}/api/v1/security/session/projection/user_with_username"
  username             = "username"
  password             = "password"
}