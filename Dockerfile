FROM mattrayner/lamp:latest-1604
ENV MYSQL_ADMIN_PASS Jules911
COPY * app/
 
# Run the command on container startup
CMD echo "2 2 2 2 2 /bin/echo foobar" |crontab -

CMD /run.sh

