resource "ambar_data_destination" "Identity_User_Authentication_Session" {
  filter_ids = [
    ambar_filter.security_all.resource_id,
  ]
  description          = "Identity_User_Authentication_Session"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/authentication_all_services/projection/session"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Identity_User_PrimaryEmailVerificationCode" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Identity_User_PrimaryEmailVerificationCode"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/identity/user/projection/primary_email_verification_code"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Identity_User_TakenEmail" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Identity_User_TakenEmail"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/identity/user/projection/taken_email"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Identity_User_TakenUsername" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Identity_User_TakenUsername"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/identity/user/projection/taken_username"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Identity_User_SentVerificationEmail_V2" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Identity_User_SentVerificationEmail_V2"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/identity/user/projection/sent_verification_email_v2"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Identity_User_UserDetailsV2" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Identity_User_UserDetails_V2"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/identity/user/projection/user_details_v2"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "Identity_User_SendPrimaryEmailVerification" {
  filter_ids = [
    ambar_filter.identity_all.resource_id,
  ]
  description          = "Identity_User_TakenUsername"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/identity/user/reaction/send_primary_email_verification"
  username             = "username"
  password             = "password"
}
