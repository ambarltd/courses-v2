# Doing all events for now, for simplicity

resource "ambar_filter" "identity_all" {
  data_source_id  = ambar_data_source.event_store.resource_id
  description     = "identity_all"
  filter_contents = "true"
}

resource "ambar_filter" "security_all" {
  data_source_id  = ambar_data_source.event_store.resource_id
  description     = "security_all"
  filter_contents = "true"
}

resource "ambar_filter" "credit_card_product" {
  data_source_id  = ambar_data_source.event_store.resource_id
  description     = "credit_card_product_all"
  filter_contents = "true"
}
