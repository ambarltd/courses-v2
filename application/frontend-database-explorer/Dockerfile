FROM node:20.11.1

# Define build arguments
ARG DB_TYPE=postgres
ARG STUDIO_PORT=5555
ARG REFRESH_INTERVAL=10

WORKDIR /app

COPY package.json .
RUN npm install
RUN npm install prisma-dbml-generator

COPY prisma-mongodb ./prisma-mongodb/
COPY prisma-postgres ./prisma-postgres/

RUN mkdir prisma && \
    if [ "$DB_TYPE" = "mongo" ]; then \
      cp prisma-mongodb/schema.prisma prisma/schema.prisma; \
    else \
      cp prisma-postgres/schema.prisma prisma/schema.prisma; \
    fi

# Create a start script that handles schema introspection and generation
RUN echo '#!/bin/sh\n\
refresh_schema() {\n\
  echo "Refreshing MongoDB schema..."\n\
  npx prisma db pull --force \n\
  if [ $? -eq 0 ]; then\n\
    echo "Schema pulled successfully, generating Prisma Client..."\n\
    npx prisma generate\n\
    sed -i "/^model/,/^}/ { /^}/ s/^}/  @@readonly\n}/ }" prisma/schema.prisma\n\
  else\n\
    echo "Failed to pull schema. Exiting to trigger container restart..."\n\
    exit 1\n\
  fi\n\
}\n\
\n\
if [ "$DB_TYPE" = "mongo" ]; then \n\
  export DATABASE_URL="$MONGO_DATABASE_URL"\n\
  echo "MongoDB detected - initializing..."\n\
  refresh_schema\n\
  # Start Prisma Studio in background\n\
  npx prisma studio --port $STUDIO_PORT --hostname 0.0.0.0 & \n\
  # Periodic refresh loop\n\
  while true; do\n\
    sleep $REFRESH_INTERVAL\n\
    refresh_schema\n\
  done\n\
else \n\
  export DATABASE_URL="$POSTGRES_DATABASE_URL"\n\
  npx prisma generate\n\
  npx prisma studio --port $STUDIO_PORT --hostname 0.0.0.0\n\
fi' > /app/start.sh

RUN chmod +x /app/start.sh

ENV POSTGRES_DATABASE_URL=""
ENV MONGO_DATABASE_URL=""
ENV REFRESH_INTERVAL=300
EXPOSE $STUDIO_PORT

CMD ["/app/start.sh"]