FROM mattrayner/lamp:latest-1604
ENV MYSQL_ADMIN_PASS Jules911
COPY * app/
CMD ["/run.sh"]
