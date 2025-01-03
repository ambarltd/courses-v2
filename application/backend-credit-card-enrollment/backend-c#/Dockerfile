# Build Stage
FROM mcr.microsoft.com/dotnet/sdk:9.0 AS build
WORKDIR /src

# Install wget
RUN apt-get update && apt-get install -y wget

# Copy csproj and restore dependencies
COPY *.csproj .
RUN dotnet restore

# Copy everything else and build
COPY . .
RUN dotnet publish -c Release -o /app

# Runtime Stage
FROM mcr.microsoft.com/dotnet/aspnet:9.0
WORKDIR /app

# Install wget in the runtime image
RUN apt-get update && apt-get install -y wget && rm -rf /var/lib/apt/lists/*

COPY --from=build /app .

# Expose port
EXPOSE 8080

ENTRYPOINT ["dotnet", "CreditCardEnrollment.dll"]
