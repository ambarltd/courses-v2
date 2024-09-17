FROM node:20.11.1


WORKDIR /app

COPY package.json /app
COPY yarn.lock /app
RUN yarn install

COPY ./src /app/src

ENV DOMAIN_IDENTITY;
ENV DOMAIN_SECURITY;

EXPOSE 8080
CMD [ "node", "src/main.js" ]


