FROM ubuntu
COPY * /var/www/html/
RUN apt-get update
RUN apt-get install -y apache2
RUN apt-get install -y apache2-utils
RUN apt-get install -y php-pear php-fpm php-dev php-zip php-curl php-xmlrpc php-gd php-mysql php-mbstring php-xml libapache2-mod-php
EXPOSE 80
ENTRYPOINT ["apachectl"]
CMD ["-DFOREGROUND"]
