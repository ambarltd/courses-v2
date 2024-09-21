resource "ambar_data_destination" "identity_event_store_proxy" {
  filter_ids = [
    ambar_filter.identity_event_store_proxy.resource_id,
  ]
  description          = "identity_event_store_proxy"
  destination_endpoint = "https://webhook.site/94a5b769-bc57-435f-9f67-a9f6d799f882"
  username             = "username"
  password             = "password"
}

resource "ambar_data_destination" "identity_event_store_direct" {
  filter_ids = [
    ambar_filter.identity_event_store_direct_ip.resource_id,
  ]
  description          = "identity_event_store_direct"
  destination_endpoint = "https://webhook.site/94a5b769-bc57-435f-9f67-a9f6d799f882"
  username             = "username"
  password             = "password"
}


##resource "ambar_data_destination" "Identity_User_PrimaryEmailVerificationCode" {
#  filter_ids = [
#    ambar_filter.identity_all.resource_id,
#  ]
#  description          = "Identity_User_PrimaryEmailVerificationCode"
#  destination_endpoint = "${var.data_destination_identity.endpoint_prefix}/api/v1/identity/user/projection/primary_email_verification_code"
#  username             = "username"
#  password             = "password"
#}
#
#resource "ambar_data_destination" "Identity_User_TakenEmail" {
#  filter_ids = [
#    ambar_filter.identity_all.resource_id,
#  ]
#  description          = "Identity_User_TakenEmail"
#  destination_endpoint = "${var.data_destination_identity.endpoint_prefix}/api/v1/identity/user/projection/taken_email"
#  username             = "username"
#  password             = "password"
#}
#
#resource "ambar_data_destination" "Identity_User_TakenUsername" {
#  filter_ids = [
#    ambar_filter.identity_all.resource_id,
#  ]
#  description          = "Identity_User_TakenUsername"
#  destination_endpoint = "${var.data_destination_identity.endpoint_prefix}/api/v1/identity/user/projection/taken_username"
#  username             = "username"
#  password             = "password"
#}
#
#resource "ambar_data_destination" "Identity_User_SendPrimaryEmailVerification" {
#  filter_ids = [
#    ambar_filter.identity_all.resource_id,
#  ]
#  description          = "Identity_User_TakenUsername"
#  destination_endpoint = "${var.data_destination_identity.endpoint_prefix}/api/v1/identity/user/reaction/send_primary_email_verification"
#  username             = "username"
#  password             = "password"
#}