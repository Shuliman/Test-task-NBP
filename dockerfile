# Начинаем с официального образа PHP с Apache. Используем PHP 8.
FROM php:8.0-apache

# Устанавливаем расширение PHP PDO для MySQL
RUN docker-php-ext-install pdo_mysql

# Включаем модуль mod_rewrite для Apache
RUN a2enmod rewrite

# Устанавливаем MySQL сервер
RUN apt-get update && apt-get install -y mariadb-server && service mariadb start

# Копируем файлы приложения в контейнер
COPY ./backend /var/www/html/
COPY ./frontend /var/www/html/frontend

# Создаем базу данных и таблицу
RUN echo "CREATE DATABASE mydb; USE mydb; CREATE TABLE IF NOT EXISTS conversion_results (id INT AUTO_INCREMENT PRIMARY KEY, amount DECIMAL(10, 4) NOT NULL, source_currency VARCHAR(3) NOT NULL, target_currency VARCHAR(3) NOT NULL, converted_amount DECIMAL(10, 4) NOT NULL, date DATETIME NOT NULL);" | mysql

# Заполняем config.php
RUN echo "<?php\n\
\n\
return [\n\
    'db' => [\n\
        'host' => 'localhost',\n\
        'dbname' => 'mydb',\n\
        'username' => 'root',\n\
        'password' => 'toor',\n\
        'tableName' => 'conversion_results',\n\
        'options' => [\n\
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n\
            PDO::ATTR_STRINGIFY_FETCHES => false,\n\
        ],\n\
    ],\n\
];\n" > /var/www/html/backend/config.php


# Меняем владельца файлов приложения на пользователя веб-сервера
RUN chown -R www-data:www-data /var/www/html

# Открываем порт 80 для веб-сервера
EXPOSE 80