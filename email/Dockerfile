FROM alpine:latest

# Install supervisor, postfix
# Install postfix first to get the first account (101)
# Install opendkim second to get the second account (102)
RUN        true && \
           apk add --no-cache --upgrade cyrus-sasl cyrus-sasl-plain cyrus-sasl-login && \
           apk add --no-cache postfix && \
           apk add --no-cache opendkim && \
           apk add --no-cache ca-certificates tzdata supervisor rsyslog && \
           apk add --no-cache --upgrade musl musl-utils && \
           (rm "/tmp/"* 2>/dev/null || true) && (rm -rf /var/cache/apk/* 2>/dev/null || true)

# Set up configuration
COPY       supervisord.conf /etc/supervisord.conf
COPY       rsyslog.conf /etc/rsyslog.conf
COPY       opendkim.conf /etc/opendkim/opendkim.conf
COPY       run.sh /run.sh
COPY       opendkim.sh /opendkim.sh
RUN        chmod +x /run.sh /opendkim.sh

# Set up volumes
VOLUME     [ "/var/spool/postfix", "/etc/postfix", "/etc/opendkim/keys" ]

# Run supervisord
USER       root
WORKDIR    /tmp

EXPOSE     587
CMD        ["/bin/sh", "-c", "/run.sh"]
