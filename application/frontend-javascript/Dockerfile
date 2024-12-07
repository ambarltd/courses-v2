FROM node:20.11.1

WORKDIR /app

COPY package.json /app
COPY yarn.lock /app
RUN yarn install

COPY ./src /app/src
COPY ./assets /app/assets

ENV DOMAIN_IDENTITY=""
ENV DOMAIN_SECURITY=""
ENV DOMAIN_CREDIT_CARD_PRODUCT=""
ENV DOMAIN_CARD_ENROLLMENT=""

EXPOSE 8080
CMD [ "node", "src/main.js" ]


