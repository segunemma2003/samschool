#!/bin/bash

# Stop Nginx server
sudo systemctl stop nginx || true

# Stop PHP-FPM service
sudo systemctl stop php8.3-fpm || true    # Adjust PHP version as needed
