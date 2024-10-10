# Step 1: Use Maven image to build the application
FROM maven:3.9.5-eclipse-temurin-21 AS build

# Set the working directory inside the container
WORKDIR /app

# Copy the pom.xml and download dependencies (layer caching)
COPY pom.xml .
RUN mvn dependency:go-offline

# Copy the rest of the application source code
COPY src ./src

# Build the Spring Boot application
RUN mvn clean package -DskipTests

# Step 2: Use JDK 21 runtime image to run the application
FROM eclipse-temurin:21-jdk

# Set the working directory for the runtime container
WORKDIR /app

# Copy the JAR file from the build image to the runtime image
COPY --from=build /app/target/*.jar app.jar

# Expose port 8080 (Spring Boot default port)
EXPOSE 8080

# Set the entry point to run the application
ENTRYPOINT ["java", "-jar", "app.jar"]
