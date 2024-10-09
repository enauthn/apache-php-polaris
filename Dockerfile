FROM php:7.4-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy the PHP application files into the container
COPY ./php/src /var/www/html

# Copy the polaris-web directory into the container
COPY ./php/polaris-web /var/www/html/polaris-web

# Expose port 80 for Apache
EXPOSE 80
