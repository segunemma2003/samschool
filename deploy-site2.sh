#!/bin/bash
set -e  # Exit immediately if a command exits with a non-zero status

REPO_URL="https://github.com/segunemma2003/samschool.git"
APP_DIR="/var/www/northstar"
BRANCH="main"

echo "Deploying Northstar from $BRANCH branch..."

# Install required PHP extensions first
echo "Installing required PHP extensions..."
sudo apt update
sudo apt install -y php8.3-intl

# Create directories with proper permissions
sudo mkdir -p $APP_DIR
sudo chown -R $USER:$USER $APP_DIR

# Check if it's a git repository
if [ -d "$APP_DIR/.git" ]; then
  # It's a git repository, so pull the latest changes
  echo "Git repository found. Pulling latest changes..."
  cd $APP_DIR
  git fetch --all
  git reset --hard origin/$BRANCH
else
  # It's not a git repository
  if [ "$(ls -A $APP_DIR)" ]; then
    # Directory is not empty and not a git repo
    echo "Directory exists but is not a git repository. Backing up and cloning fresh..."
    BACKUP_DIR="/var/www/northstar_backup_$(date +%Y%m%d%H%M%S)"
    sudo mv $APP_DIR $BACKUP_DIR
    sudo mkdir -p $APP_DIR
    sudo chown -R $USER:$USER $APP_DIR
    git clone -b $BRANCH $REPO_URL $APP_DIR
    cd $APP_DIR

    # Copy .env from backup if it exists
    if [ -f "$BACKUP_DIR/.env" ]; then
      cp $BACKUP_DIR/.env $APP_DIR/.env
      echo "Copied existing .env file from backup"
    fi
  else
    # Directory is empty, just clone
    echo "Directory is empty. Cloning repository..."
    git clone -b $BRANCH $REPO_URL $APP_DIR
    cd $APP_DIR
  fi
fi

# Check for composer
if ! command -v composer &> /dev/null; then
  echo "Installing composer..."
  curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install dependencies with fallback options
echo "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev || {
  echo "Standard composer install failed, trying with ignore-platform-req..."
  composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-req=ext-intl || {
    echo "Composer install failed completely. Check for missing PHP extensions."
    exit 1
  }
}



# Create storage directories
mkdir -p $APP_DIR/storage/app $APP_DIR/storage/framework/cache $APP_DIR/storage/framework/sessions $APP_DIR/storage/framework/views $APP_DIR/storage/logs
mkdir -p $APP_DIR/bootstrap/cache

# Database migrations (with error handling)
php artisan migrate --force || { echo "Migration failed, but continuing..."; }
php artisan tenants:migrate --force || { echo "Migration failed, but continuing..."; }

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
echo "Restarting supervisor services..."
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart northstar-worker:* || {
  echo "Supervisor restart failed, but continuing...";
}
# Reload Nginx (with error handling)
sudo systemctl reload nginx || { echo "Nginx reload failed. Check the configuration."; }

echo "NorthStar deployment completed successfully!"
