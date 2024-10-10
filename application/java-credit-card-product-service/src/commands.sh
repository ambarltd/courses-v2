endpoint="your-endpoint-here"

# To create the Starter card
curl -X POST "${endpoint}/api/v1/credit_card_product/product" \
-H "Content-Type: application/json" \
-d '{
  "productIdentifierForAggregateIdHash": "STARTER_CREDIT_CARD",
  "name": "Starter",
  "interestInBasisPoints": 1200,
  "annualFeeInCents": 5000,
  "paymentCycle": "monthly",
  "creditLimitInCents": 50000,
  "maxBalanceTransferAllowedInCents": 0,
  "reward": "none",
  "cardBackgroundHex": "#7fffd4"
}'

# To create the Platinum card
curl -X POST "${endpoint}/api/v1/credit_card_product/product" \
-H "Content-Type: application/json" \
-d '{
  "productIdentifierForAggregateIdHash": "PLATINUM_CREDIT_CARD",
  "name": "Platinum",
  "interestInBasisPoints": 300,
  "annualFeeInCents": 50000,
  "paymentCycle": "monthly",
  "creditLimitInCents": 500000,
  "maxBalanceTransferAllowedInCents": 100000,
  "reward": "points",
  "cardBackgroundHex": "#E5E4E2"
}'

# To list the current card products
curl -X POST "${endpoint}/api/v1/credit_card_product/product/list-items" | jq .

productId=""
# Activate a product
curl -X POST "${endpoint}/api/v1/credit_card_product/product/activate/${productId}"

# Deactivate a product
curl -X POST "${endpoint}/api/v1/credit_card_product/product/deactivate/${productId}"