# syntax=docker/dockerfile:1
FROM hashicorp/terraform:1.8.2

RUN apk update
RUN apk add --no-cache bash
RUN apk add --no-cache aws-cli
RUN apk add --no-cache docker-cli
RUN apk add --no-cache jq

COPY .terraform.entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

RUN mkdir "/var/build-files -p"
WORKDIR "/var/build-files"