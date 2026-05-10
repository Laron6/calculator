**Дипломный проект** — веб-сервис для расчёта производительности труда по формуле **ПТ = V / T** (объём продукции / затраченное время).

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

---

## ✨ Основные возможности

- Добавление, редактирование и удаление рабочих
- Создание рабочих групп и управление составом
- Ввод объёма продукции и затраченного времени для каждого рабочего
- **Расчёт производительности труда по формуле ПТ = V / T**
- Визуализация результатов в виде линейных графиков (Chart.js)
- Импорт/экспорт списка рабочих через `.lst` файл (совместимость с Delphi)
- Современный glassmorphism-дизайн
- Регистрация и вход с проверкой сложного пароля
- Управление активными сессиями и устройствами
- Тёмная и светлая темы оформления
- Полностью адаптивная вёрстка (мобильные устройства)
- Middleware для отслеживания активности пользователя

---

## 📊 Формула расчёта

**Производительность труда** = **V / T**, где:
- **V** — объём выпущенной продукции (штуки)
- **T** — затраченное время (часы)

Результат отображается в **шт/чел\*ч** и показывает эффективность труда каждого работника.

---

## 🛠 Технологии

| Компонент | Технология |
|-----------|------------|
| Backend | Laravel 11 + PHP 8.2 |
| Frontend | Blade + CSS + Vanilla JS |
| Графики | Chart.js |
| База данных | PostgreSQL 16+ / SQLite |
| Иконки | Font Awesome 6 |
| Шрифты | Google Fonts (Poppins) |

---

## 🚀 Установка

### Требования
- Ubuntu 22.04 LTS или новее
- PHP 8.2+
- PostgreSQL 16+
- Nginx
- Git
- Composer

### Установка и запуск

```bash
# 1. Клонируем репозиторий
git clone https://github.com/Laron6/calculator.git
cd calculator

# 2. Устанавливаем зависимости
composer install

# 3. Обновляем систему
sudo apt update
sudo apt upgrade -y

# 4. Устанавливаем Nginx
sudo apt install nginx -y

# 5. Устанавливаем PHP и расширения
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl -y

# 6. Устанавливаем PostgreSQL
sudo apt install postgresql postgresql-contrib -y

# 7. Устанавливаем Git и Composer
sudo apt install git -y
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 8. Настраиваем PostgreSQL
sudo -u postgres psql <<EOF
CREATE DATABASE productivity_db;
CREATE USER productivity_user WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE productivity_db TO productivity_user;
\q
EOF

# 9. Размещаем файлы проекта для production
cd /var/www
git clone https://github.com/ArmdlTech/vkr-service-calc-productivity-assessment-work-groups.git productivity-site
cd productivity-site
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env  # Настройте подключение к БД (укажите DATABASE_URL или параметры ниже)

# 10. Настраиваем окружение
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 11. Настраиваем Nginx
sudo tee /etc/nginx/sites-available/productivity > /dev/null <<'EOF'
server {
    listen 80;
    server_name ваш-домен.ru;
    root /var/www/productivity-site/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# 12. Активируем сайт и перезапускаем Nginx
sudo ln -s /etc/nginx/sites-available/productivity /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx

# 13. Устанавливаем SSL-сертификат (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d ваш-домен.ru

# 14. Настраиваем резервное копирование БД
sudo tee /usr/local/bin/backup.sh > /dev/null <<'EOF'
#!/bin/bash
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
pg_dump productivity_db > $BACKUP_DIR/db_$DATE.sql
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/productivity-site
find $BACKUP_DIR -type f -mtime +30 -delete
EOF
sudo chmod +x /usr/local/bin/backup.sh
(crontab -l 2>/dev/null; echo "0 2 * * * /usr/local/bin/backup.sh") | crontab -

# 15. Настраиваем обновление приложения
sudo tee /usr/local/bin/update-app.sh > /dev/null <<'EOF'
#!/bin/bash
cd /var/www/productivity-site
git pull
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
EOF
sudo chmod +x /usr/local/bin/update-app.sh

# 16. Копируем окружение
cp .env.example .env

# 17. Настраиваем базу данных (выберите один из вариантов)

# Вариант А: PostgreSQL
createdb -U postgres productivity_db
# В .env укажите:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=productivity_db
# DB_USERNAME=postgres
# DB_PASSWORD=admin

# Вариант Б: SQLite (быстрый старт без установки PostgreSQL)
touch database/database.sqlite
# В .env укажите:
# DB_CONNECTION=sqlite
# DB_DATABASE=/полный/путь/к/project/database/database.sqlite

# 18. Генерируем ключ приложения
php artisan key:generate

# 19. Выполняем миграции и заполняем БД тестовыми данными
php artisan migrate --seed

# 20. Запускаем сервер разработки
php artisan serve

# 21. Запускаем все тесты
php artisan test