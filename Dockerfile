# Use a imagem oficial do PHP com Apache como base
FROM php:8.1-apache

# Instalar dependências e extensões necessárias para o Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_mysql

# Ativar o mod_rewrite do Apache
RUN a2enmod rewrite

# Configurar o diretório de trabalho dentro do container
WORKDIR /var/www/html

# Copiar o código do projeto Laravel para o container
COPY . .

# Instalar as dependências do Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Definir permissões para o Laravel
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expor a porta 80 (padrão do Apache)
EXPOSE 80

# Iniciar o Apache
CMD ["apache2-foreground"]
