FROM jbirch8865/lamp:latest
ENV MYSQL_ADMIN_PASS Jules911
CMD bash /run.sh
CMD service mysql start 
CMD /usr/sbin/apache2ctl -D FOREGROUND
