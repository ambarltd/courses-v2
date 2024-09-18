set -e

if [ -f .env ]
then
  source .env
fi

[ "$1" = "apply" ] && TERRAFORM_COMMAND="apply -auto-approve" || TERRAFORM_COMMAND="plan"
GIT_COMMIT=$(git log -1 --pretty=format:"%H")

docker build -t drew/terraform-runner --file .github/workflows/.terraform.Dockerfile .github/workflows
echo "DOCKER IMAGE BUILT"

docker run \
  --env AWS_ACCESS_KEY="${AWS_ACCESS_KEY_ID}" \
  --env AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY}" \
  --env AWS_SESSION_TOKEN="${AWS_SESSION_TOKEN}" \
  --env AWS_DEFAULT_REGION="$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_region -r)" \
  --mount "type=bind,source=${PWD}/infrastructure,target=/var/build-files"  \
  --mount "type=bind,source=${PWD}/application,target=/var/application"  \
  --volume /var/run/docker.sock:/var/run/docker.sock \
  drew/terraform-runner init -upgrade \
  -backend-config="dynamodb_table=$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_table -r)" \
  -backend-config="bucket=$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_bucket -r)" \
  -backend-config="key=$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_key -r)" \
  -backend-config="region=$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_region -r)"

docker run \
  --env AWS_ACCESS_KEY="${AWS_ACCESS_KEY_ID}" \
  --env AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY}" \
  --env AWS_SESSION_TOKEN="${AWS_SESSION_TOKEN}" \
  --env AWS_DEFAULT_REGION="$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_region -r)" \
  --mount "type=bind,source=${PWD}/infrastructure,target=/var/build-files" \
  --mount "type=bind,source=${PWD}/application,target=/var/application" \
  --volume /var/run/docker.sock:/var/run/docker.sock \
  drew/terraform-runner $TERRAFORM_COMMAND \
  -var="credentials_base64=${CREDENTIALS_BASE64}" \
  -var="git_commit_hash=${GIT_COMMIT}"

docker run \
  --env AWS_ACCESS_KEY="${AWS_ACCESS_KEY_ID}" \
  --env AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY}" \
  --env AWS_SESSION_TOKEN="${AWS_SESSION_TOKEN}" \
  --env AWS_DEFAULT_REGION="$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_region -r)" \
  --mount "type=bind,source=${PWD}/infrastructure,target=/var/build-files" \
  --mount "type=bind,source=${PWD}/application,target=/var/application" \
  --volume /var/run/docker.sock:/var/run/docker.sock \
  drew/terraform-runner output -json production_backend_connection_outputs > /tmp/courses-v2.output.tmp.tf

cat /tmp/courses-v2.output.tmp.tf | tail -n 1 > /tmp/courses-v2.output.tf

docker run \
  --env AWS_ACCESS_KEY="${AWS_ACCESS_KEY_ID}" \
  --env AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY}" \
  --env AWS_SESSION_TOKEN="${AWS_SESSION_TOKEN}" \
  --env AWS_DEFAULT_REGION="$(echo $STATE_MANAGEMENT_BASE64 | base64 --decode | jq .state_management_region -r)" \
  --mount "type=bind,source=${PWD}/infrastructure,target=/var/build-files" \
  --mount "type=bind,source=${PWD}/application,target=/var/application" \
  --volume /var/run/docker.sock:/var/run/docker.sock \
  drew/terraform-runner output -json public_domains

echo "================== SEE ABOVE FOR YOUR PUBLIC DOMAINS =================="
