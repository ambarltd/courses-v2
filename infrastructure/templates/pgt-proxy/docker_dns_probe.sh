#!/bin/bash
set -e


eval "$(jq -r '@sh "PRIVATE_KEY_PEM_IN_BASE64=\(.private_key_pem_in_base64) DNS_PROBE_SSH_KEY_FILENAME=\(.dns_probe_ssh_key_filename) DNS_PROBE_INSTANCE_PUBLIC_IP=\(.dns_probe_instance_public_ip) DATABASE_LOCA_NETWORK_PUBLIC_IP_ADDRESS=\(.database_local_network_ip_address) DNS_PROBE_RESOLVED_FILENAME=\(.dns_probe_resolved_filename)"')"

# base64 --decode doesn't work in alpine images, needs -d instead
echo $PRIVATE_KEY_PEM_IN_BASE64 | base64 -d  > $DNS_PROBE_SSH_KEY_FILENAME
chmod 600 $DNS_PROBE_SSH_KEY_FILENAME

ssh -oStrictHostKeyChecking=no -i $DNS_PROBE_SSH_KEY_FILENAME \
  probe_user@$DNS_PROBE_INSTANCE_PUBLIC_IP \
  "echo | openssl s_client -starttls postgres -connect $DATABASE_LOCA_NETWORK_PUBLIC_IP_ADDRESS:5432 2>/dev/null | openssl x509 -noout -text" 2>/devnull | \
  grep 'sql.goog' | awk '{print $NF}'  | tr -d 'DNS:' | tr -d '\n' \
  > $DNS_PROBE_RESOLVED_FILENAME


cat $DNS_PROBE_RESOLVED_FILENAME | grep "sql.goog" >> /dev/null
MY_RESULT=$(cat $DNS_PROBE_RESOLVED_FILENAME)

rm $DNS_PROBE_SSH_KEY_FILENAME
rm $DNS_PROBE_RESOLVED_FILENAME

jq -n --arg result "$MY_RESULT" '{"database_tls_host":$result}'