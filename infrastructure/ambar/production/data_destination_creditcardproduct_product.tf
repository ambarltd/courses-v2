resource "ambar_data_destination" "CreditCardProduct_Product" {
  filter_ids = [
    ambar_filter.credit_card_product.resource_id,
  ]
  description          = "CreditCardProduct_Product_ProductListItem"
  destination_endpoint = "${var.data_destination_credit_card_product.endpoint_prefix}/api/v1/credit_card_product/product/projection"
  username             = "username"
  password             = "password"
}
