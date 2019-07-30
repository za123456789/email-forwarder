FROM zeshan/alpine-postfix

# install postsrsd
RUN set -ex && \
    apk add --update --no-cache postsrsd && \
    rm -rf /var/cache/apk/* 

COPY ../email/init.sh /init.sh && \
   ../email/postsrsd.secret /etc/postsrsd/postsrsd.secret

EXPOSE 10001 10002

CMD ["/init.sh"]


