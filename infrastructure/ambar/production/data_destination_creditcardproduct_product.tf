resource "ambar_data_destination" "CreditCardProduct_Authentication_Session" {
  filter_ids = [
    ambar_filter.security_all.resource_id,
  ]
  description          = "CreditCardProduct_Authentication_Session"
  destination_endpoint = "${var.data_destination_credit_card_product.endpoint_prefix}/api/v1/authentication_all_services/projection/session"
  username             = "username"
  password             = "password"
}


resource "ambar_data_destination" "CreditCardProduct_Product_ProductListItem" {
  filter_ids = [
    ambar_filter.credit_card_product.resource_id,
  ]
  description          = "CreditCardProduct_Product_ProductListItem"
  destination_endpoint = "${var.data_destination_credit_card_product.endpoint_prefix}/api/v1/credit_card_product/product/projection/product_list_item"
  username             = "username"
  password             = "password"
}
