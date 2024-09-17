FROM node:20.11.1

CMD yarn install

WORKDIR /app
COPY . /app

ENV DOMAIN_IDENTITY="https://a34f7853b5c9b-pro-mon-app-app-323035764089.europe-west2.run.app/api/v1/identity"
ENV DOMAIN_SECURITY="https://a34f7853b5c9b-pro-mon-app-app-323035764089.europe-west2.run.app/api/v1/security"

EXPOSE 8080
CMD [ "node", "src/main.js" ]


