# Using docker
#
# ```bash
# docker build -t local/opencage-php .
# docker build -t local/opencage-php:8.3 --build-arg PHP_VERSION=8.3 .   # any other PHP version
#
# docker run -ti -v `pwd`:/usr/src/myapp local/opencage-php
#
# app@ee88ad785ca2:/usr/src/myapp$ composer install
# app@ee88ad785ca2:/usr/src/myapp$ ./vendor/bin/phpunit
# app@ee88ad785ca2:/usr/src/myapp$ ./vendor/bin/phpcs .
# app@ee88ad785ca2:/usr/src/myapp$ ./vendor/bin/phpstan analyse
# ```

ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-cli

# composer needs unzip to extract downloaded packages
RUN apt-get update -qq && \
    apt-get install -y -qq vim unzip && \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN useradd --user-group --system --create-home --no-log-init app

COPY . /usr/src/myapp
WORKDIR /usr/src/myapp

RUN chown -R app:app /usr/src/myapp

USER app

VOLUME [ "/usr/src/myapp" ]

CMD ["/bin/bash"]
