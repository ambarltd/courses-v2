FROM ambarltd/pgt-proxy:v1.1

COPY trust_this_ca.pem /etc/pgt_proxy/client_tls/custom_cas/
COPY tls_private_key.pem /etc/pgt_proxy/server_tls/key.pem
COPY tls_certificate.pem /etc/pgt_proxy/server_tls/certificate.pem

CMD ["--server-private-key-path", "/etc/pgt_proxy/server_tls/key.pem", \
    "--server-certificate-path", "/etc/pgt_proxy/server_tls/certificate.pem", \
    "--server-port", "5432", \
    "--client-connection-host-or-ip", "CONNECTION_HOST_OR_IP_TO_BE_REPLACED", \
    "--client-connection-port", "5432", \
    "--client-tls-validation-host", "TLS_VALIDATION_HOST_TO_BE_REPLACED", \
    "--client-ca-roots-path", "/etc/pgt_proxy/client_tls/custom_cas/"]