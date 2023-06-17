FROM php:8.0-apache

#Install PHP PDO extension for MySQL
RUN docker-php-ext-install pdo_mysql

#Enabling the mod_rewrite module for Apache
RUN a2enmod rewrite

#Installing the MySQL server
RUN apt-get update && apt-get install -y mariadb-server 

#Copying the application files into a container
COPY ./backend /var/www/html/backend
COPY ./frontend /var/www/html/frontend

#Filling out config.php
RUN printf "<?php\n\
\n\
return [\n\
    'db' => [\n\
        'host' => '127.0.0.1',\n\
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

#Change the owner of the application files to a web server user
RUN chown -R www-data:www-data /var/www/html

#Open port for the web server
EXPOSE 443

#Create an initialization script
RUN printf "#!/bin/bash\n\
service mariadb start\n\
mysql -uroot -e \"ALTER USER 'root'@'localhost' IDENTIFIED BY 'toor';\"\n\
mysql -uroot -ptoor -e \"FLUSH PRIVILEGES;\"\n\
mysql -uroot -ptoor -e \"CREATE DATABASE IF NOT EXISTS mydb; USE mydb; CREATE TABLE IF NOT EXISTS conversion_results (id INT AUTO_INCREMENT PRIMARY KEY, amount DECIMAL(10, 4) NOT NULL, source_currency VARCHAR(3) NOT NULL, target_currency VARCHAR(3) NOT NULL, converted_amount DECIMAL(10, 4) NOT NULL, date DATETIME NOT NULL);\"\n\
apache2-foreground\n" > /usr/local/bin/init.sh \
&& chmod +x /usr/local/bin/init.sh

#Run the initialization script when the container starts
CMD ["/usr/local/bin/init.sh"]

