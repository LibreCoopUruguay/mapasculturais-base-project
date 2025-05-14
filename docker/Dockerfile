FROM hacklab/mapasculturais:v7.6-beta

COPY themes /var/www/src/themes
COPY plugins /var/www/src/plugins

WORKDIR /var/www/src
RUN pnpm install --recursive && pnpm run build 
WORKDIR /var/www

COPY docker/common/config.d /var/www/config/common.d
COPY docker/production/config.d /var/www/config/config.d
COPY docker/common/timezone.ini /usr/local/etc/php/conf.d/timezone.ini
# COPY translations_tmp /var/www/src/translations

COPY src/modules/FAQ/questions/es_ES /var/www/src/modules/FAQ/questions/pt_BR

COPY hardcode/agent-types.php /var/www/src/conf/agent-types.php
COPY hardcode/space-types.php /var/www/src/conf/space-types.php
COPY hardcode/subsite-types.php /var/www/src/conf/subsite-types.php
COPY hardcode/event-types.php /var/www/src/conf/event-types.php
COPY hardcode/project-types.php /var/www/src/conf/project-types.php
COPY hardcode/taxonomies.php /var/www/src/conf/taxonomies.php
COPY hardcode/states-and-cities.php /var/www/src/conf/states-and-cities.php
