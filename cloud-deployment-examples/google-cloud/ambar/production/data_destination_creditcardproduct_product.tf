resource "ambar_data_destination" "CreditCardProduct_Product_projector" {
  filter_ids = [
    ambar_filter.credit_card_product.resource_id,
  ]
  description          = "CreditCardProduct_Product_ProductListItem"
  destination_endpoint = "${var.data_destination_backend_php.endpoint_prefix}/api/v1/credit_card_product/product/projection"
  username             = "username"
  password             = "password"
}
