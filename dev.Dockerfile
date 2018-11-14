FROM mattrayner/lamp:latest-1604
COPY * app/
VOLUME /app
CMD ["/run.sh"]
