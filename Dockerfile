FROM mattrayner/lamp:latest-1604
COPY * app/
CMD ["/run.sh"]
