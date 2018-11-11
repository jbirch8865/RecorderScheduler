FROM ubuntu
COPY * /var/www/html/
RUN apt-get update
RUN apt-get install -y apache2
RUN apt-get install -y apache2-utils
EXPOSE 80
ENTRYPOINT ["apachectl"]
CMD ["-DFOREGROUND"]
