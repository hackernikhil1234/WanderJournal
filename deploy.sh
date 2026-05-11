#!/bin/bash
# WanderJournal Production Deployment Script

echo "🚀 Starting WanderJournal Deployment..."

# 1. Enter Maintenance Mode
echo "⏸️ Entering maintenance mode..."
php artisan down --secret="wanderjournal-deploy"

# 2. Install Dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "📦 Installing Node dependencies..."
npm ci
npm run build

# 3. Optimize Laravel
echo "⚙️ Optimizing Laravel Framework..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# 4. Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. Clear Caches
echo "🧹 Clearing old application caches..."
php artisan cache:clear

# 6. Restart Queue Workers
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# 7. Exit Maintenance Mode
echo "▶️ Exiting maintenance mode..."
php artisan up

echo "✅ Deployment completed successfully!"
