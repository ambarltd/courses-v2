FROM node:20.11.1

WORKDIR /app

COPY package.json /app
COPY yarn.lock /app
RUN yarn install

COPY ./src /app/src
COPY ./assets /app/assets

ENV DOMAIN_IDENTITY="172.30.0.105"
ENV DOMAIN_SECURITY="172.30.0.105"
ENV DOMAIN_CREDIT_CARD_PRODUCT="172.30.0.101"

EXPOSE 8080
CMD [ "node", "src/main.js" ]


