FROM thruio/docker-webapp:php7

ADD . /app

RUN rm -f /var/www/html && ln -s /app/public /var/www/html