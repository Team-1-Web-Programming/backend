# FROM node:20 as node

FROM wyveo/nginx-php-fpm:php82

# COPY --from=node /usr/local/bin/node /usr/local/bin/
# COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
# RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# RUN apt-get update && apt-get install -y \
#     nginx \
#     zip \
#     unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN apt-get install php8.2-mysql

WORKDIR /usr/share/nginx/html

COPY . .
# RUN cat .env
# COPY composer.json .
# COPY composer.lock .
# COPY artisan .
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

# COPY package.json .
# COPY package-lock.json .
# RUN npm install
# RUN npm run build

COPY deploy/nginx/nginx.conf /etc/nginx/conf.d/default.conf

RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80