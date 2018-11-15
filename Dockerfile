FROM jbirch8865/lamp:latest
ENV MYSQL_ADMIN_PASS Jules911
CMD bash /run.sh ; service mysql start ; /usr/sbin/apache2ctl -D FOREGROUND
