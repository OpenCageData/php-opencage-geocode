# Using docker
# 
# ```bash
# docker build -t local/opencage-php .
# 
# docker run -ti -v `pwd`:/usr/src/myapp local/opencage-php
#
# app@ee88ad785ca2:/usr/src/myapp$ composer install
# app@ee88ad785ca2:/usr/src/myapp$ ./vendor/bin/phpunit
# app@ee88ad785ca2:/usr/src/myapp$ SKIP_CURL=1 ./vendor/bin/phpunit
# app@ee88ad785ca2:/usr/src/myapp$ ./vendor/bin/phpcs .
# app@ee88ad785ca2:/usr/src/myapp$ ./vendor/bin/phpstan analyse --level 5 src tests demo
# ```

FROM ubuntu:22.04

ENV TZ=Europe/London

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update -qq && \
    apt-get install -y -qq curl vim php-cli php-curl php-xml php-mbstring unzip 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN useradd --user-group --system --create-home --no-log-init app

COPY . /usr/src/myapp
WORKDIR /usr/src/myapp

RUN chown -R app:app /usr/src/myapp

USER app

VOLUME [ "/usr/src/myapp" ]

CMD ["/bin/bash"]