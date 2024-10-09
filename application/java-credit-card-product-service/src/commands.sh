set endpoint="your-endpoint-from-github-action"

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