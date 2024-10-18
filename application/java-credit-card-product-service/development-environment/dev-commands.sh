endpoint="localhost:8080"

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

# To modify a card product
curl -X PATCH "${endpoint}/api/v1/credit_card_product/product" \
-H "Content-Type: application/json" \
-d '{
  "id": "806ea870-56aa-4289-ac8f-76861b27a702",
  "annualFeeInCents": 50000,
  "creditLimitInCents": 500000,
  "paymentCycle": "monthly",
  "cardBackgroundHex": "#E5E4E2"
}'

# For connecting to the postgres container (To See the events)
# It will prompt for the password: my_es_password
psql -h 172.30.0.102 -p 5432 -U my_es_username -d my_es_database