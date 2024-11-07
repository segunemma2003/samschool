#!/bin/bash

# Start PHP-FPM service
sudo systemctl start php8.3-fpm || true    # Adjust PHP version as needed

# Start Nginx server
sudo systemctl start nginx || true
