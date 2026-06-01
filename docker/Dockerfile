FROM hacklab/mapasculturais:7.6.22

# Instalar y generar el locale de español para que funcionen las traducciones Gettext
# (Ignoramos fallos en apt-get update provocados por firmas obsoletas de repositorios externos)
RUN (apt-get update || true) && apt-get install -y locales && locale-gen es_ES.UTF-8

COPY themes /var/www/src/themes
COPY plugins /var/www/src/plugins

WORKDIR /var/www/src
RUN pnpm install --recursive && pnpm run build 
WORKDIR /var/www

COPY docker/common/config.d /var/www/config/common.d
COPY docker/production/config.d /var/www/config/config.d
COPY docker/common/timezone.ini /usr/local/etc/php/conf.d/timezone.ini
RUN rm -rf /var/www/src/modules/FAQ/questions/pt_BR && ln -s /var/www/src/themes/themeCulturaenlinea/modules/FAQ/questions/es_ES /var/www/src/modules/FAQ/questions/pt_BR
