#!/bin/bash
REPO_URL="https://github.com/yourusername/your-repo.git"
APP_DIR="/var/www/northstar"
BRANCH="prod"

echo "Deploying Site 2 from $BRANCH branch..."

# Clone or pull latest code
if [ -d "$APP_DIR" ]; then
  cd $APP_DIR
  git fetch --all
  git reset --hard origin/$BRANCH
else
  git clone -b $BRANCH $REPO_URL $APP_DIR
  cd $APP_DIR
fi

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Environment setup (if first time)
if [ ! -f "$APP_DIR/.env" ]; then
  cp .env.example .env
  php artisan key:generate
fi

# Database migrations
php artisan migrate --force

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear previous caches first (optional but recommended)
php artisan optimize:clear

# Set permissions
sudo chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# Restart services for this site only
sudo supervisorctl restart northstar-worker:*
sudo systemctl reload nginx

echo "Site 2 deployment completed!"
