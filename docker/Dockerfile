FROM hacklab/mapasculturais:7.6.22

COPY themes /var/www/src/themes
COPY plugins /var/www/src/plugins

WORKDIR /var/www/src
RUN pnpm install --recursive && pnpm run build 
WORKDIR /var/www

COPY docker/common/config.d /var/www/config/common.d
COPY docker/production/config.d /var/www/config/config.d
COPY docker/common/timezone.ini /usr/local/etc/php/conf.d/timezone.ini
RUN rm -rf /var/www/src/modules/FAQ/questions/pt_BR && ln -s /var/www/src/themes/themeCulturaenlinea/modules/FAQ/questions/es_ES /var/www/src/modules/FAQ/questions/pt_BR


COPY themes/themeCulturaenlinea/php-mod/taxonomies.php /var/www/src/conf/taxonomies.php

