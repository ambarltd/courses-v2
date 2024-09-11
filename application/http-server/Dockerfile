# Use the official PHP 8.3 image from Docker Hub
FROM php:8.3-cli

# Set the working directory to /srv
WORKDIR /srv

# Copy the local project files to the working directory
COPY code /srv

# Expose port 8080 to be used by the PHP built-in web server
EXPOSE 8080

# Command to run PHP's built-in server, serving files from /srv/public
CMD [ "php", "-S", "0.0.0.0:8080", "-t", "public" ]
