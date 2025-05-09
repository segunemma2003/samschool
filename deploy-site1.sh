#!/bin/bash
set -e  # Exit immediately if a command exits with a non-zero status

REPO_URL="https://github.com/segunemma2003/samschool.git"
APP_DIR="/var/www/compasse"
BRANCH="main"

echo "Deploying Compasse from $BRANCH branch..."

# Create directories with proper permissions
sudo mkdir -p $APP_DIR
sudo chown -R $USER:$USER $APP_DIR

# Clone or pull latest code
if [ -d "$APP_DIR/.git" ]; then  # Check for .git directory instead
  cd $APP_DIR
  git fetch --all
  git reset --hard origin/$BRANCH
else
  git clone -b $BRANCH $REPO_URL $APP_DIR
  cd $APP_DIR
fi

# Check for composer
if ! command -v composer &> /dev/null; then
  echo "Installing composer..."
  curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || { echo "Composer install failed"; exit 1; }

# Environment setup (if first time)
if [ ! -f "$APP_DIR/.env" ]; then
  if [ -f "$APP_DIR/.env.example" ]; then
    cp .env.example .env
    php artisan key:generate
  else
    echo "ERROR: .env.example file not found!"
    exit 1
  fi
fi

# Create storage directories
mkdir -p $APP_DIR/storage/app $APP_DIR/storage/framework/cache $APP_DIR/storage/framework/sessions $APP_DIR/storage/framework/views $APP_DIR/storage/logs
mkdir -p $APP_DIR/bootstrap/cache

# Database migrations (with error handling)
php artisan migrate --force || { echo "Migration failed, but continuing..."; }

# Clear previous caches first
php artisan optimize:clear || { echo "Optimize clear failed, but continuing..."; }

# Cache configurations
php artisan config:cache || { echo "Config cache failed, but continuing..."; }
php artisan route:cache || { echo "Route cache failed, but continuing..."; }
php artisan view:cache || { echo "View cache failed, but continuing..."; }

# Set permissions
sudo chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Restart supervisor (with error handling)
sudo supervisorctl restart compasse-worker:* || { echo "Supervisor restart failed. May need to create the configuration first or check logs."; }

# Reload Nginx (with error handling)
sudo systemctl reload nginx || { echo "Nginx reload failed. Check the configuration."; }

echo "Compasse deployment completed successfully!"
