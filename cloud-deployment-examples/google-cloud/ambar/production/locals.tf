locals {
  filters = {
    example_signed_up                                          = <<EOT
    lookup("event_name") == "Identity_User_SignedUp"
    EOT
    all                                                        = <<EOT
    lookup("event_name") == "level.billing.dispute.reported" ||
    lookup("event_name") == "level.billing.dispute.status_changed"
    EOT
  }
}