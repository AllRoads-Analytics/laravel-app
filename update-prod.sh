cd /home/forge/app.allroadsanalytics.com

git fetch
git reset --hard origin/master

composer install --no-interaction --prefer-dist --optimize-autoloader

( flock -w 10 9 || exit 1
    echo 'Restarting FPM...'; sudo -S service php8.1-fpm reload ) 9>/tmp/fpmlock

if [ -f artisan ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan migrate --force
fi

npm install
npm run prod
