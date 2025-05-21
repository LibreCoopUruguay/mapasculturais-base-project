FROM hacklab/mapasculturais:v7.6-beta

COPY themes /var/www/src/themes
COPY plugins /var/www/src/plugins

WORKDIR /var/www/src
RUN pnpm install --recursive && pnpm run build 
WORKDIR /var/www

COPY docker/common/config.d /var/www/config/common.d
COPY docker/production/config.d /var/www/config/config.d
COPY docker/common/timezone.ini /usr/local/etc/php/conf.d/timezone.ini
COPY themes/themeCulturaenlinea/php-mod/modules/FAQ/questions/es_ES /var/www/src/modules/FAQ/questions/pt_BR
COPY themes/themeCulturaenlinea/php-mod/agent-types.php /var/www/src/conf/agent-types.php
COPY themes/themeCulturaenlinea/php-mod/space-types.php /var/www/src/conf/space-types.php
COPY themes/themeCulturaenlinea/php-mod/subsite-types.php /var/www/src/conf/subsite-types.php
COPY themes/themeCulturaenlinea/php-mod/event-types.php /var/www/src/conf/event-types.php
COPY themes/themeCulturaenlinea/php-mod/project-types.php /var/www/src/conf/project-types.php
COPY themes/themeCulturaenlinea/php-mod/taxonomies.php /var/www/src/conf/taxonomies.php
COPY themes/themeCulturaenlinea/php-mod/states-and-cities.php /var/www/src/conf/states-and-cities.php
COPY themes/themeCulturaenlinea/php-mod/uruguay.php /var/www/src/modules/Entities/states-and-cities/brasil.php
