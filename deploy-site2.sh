#!/bin/bash
REPO_URL="https://github.com/yourusername/your-repo.git"
APP_DIR="/var/www/northstar"
BRANCH="prod"

echo "Deploying Northstar from $BRANCH branch..."

sudo mkdir -p $APP_DIR
sudo chown -R $USER:$USER $APP_DIR
# Clone or pull latest code
if [ -d "$APP_DIR" ]; then
  cd $APP_DIR
  git fetch --all
  git reset --hard origin/$BRANCH
else
  git clone -b $BRANCH $REPO_URL $APP_DIR
  cd $APP_DIR
fi

if ! command -v composer &> /dev/null; then
  echo "Installing composer..."
  curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Environment setup (if first time)
if [ ! -f "$APP_DIR/.env" ]; then
  cp .env.example .env
  php artisan key:generate
fi


mkdir -p $APP_DIR/storage/app $APP_DIR/storage/framework/cache $APP_DIR/storage/framework/sessions $APP_DIR/storage/framework/views $APP_DIR/storage/logs
mkdir -p $APP_DIR/bootstrap/cache

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
