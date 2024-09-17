FROM node:20.11.1


WORKDIR /app

COPY package.json /app
COPY yarn.lock /app
RUN yarn install

COPY ./src /app/src

ENV DOMAIN_IDENTITY="https://placeholder.com"
ENV DOMAIN_SECURITY="https://placeholder.com"

EXPOSE 8080
CMD [ "node", "src/main.js" ]


