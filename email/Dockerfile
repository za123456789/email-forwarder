FROM alpine:latest

RUN        true && \
           apk add --no-cache --upgrade cyrus-sasl cyrus-sasl-plain cyrus-sasl-login && \
           apk add --no-cache postfix 			\
           					  opendkim 			        \
           					  bash 				          \
           					  clamav 			          \
           					  clamav-libunrar 	    \           
           					  ca-certificates 	    \
           					  tzdata 			          \
           					  supervisor 		        \
           					  rsyslog 		       && \	
           apk add --no-cache --upgrade musl musl-utils && \
           (rm "/tmp/"* 2>/dev/null || true) && (rm -rf /var/cache/apk/* 2>/dev/null || true)

RUN        addgroup spamassassin && \        
           adduser -S -D -G spamassassin -h /var/lib/spamassassin/ spamassassin && \
           apk add --no-cache razor spamassassin 

RUN        sa-update && \
           freshclam

# Set up configuration
COPY       ./email/supervisord.conf /etc/supervisord.conf
COPY       ./email/rsyslog.conf /etc/rsyslog.conf
COPY       ./email/opendkim.conf /etc/opendkim/opendkim.conf
COPY       ./email/run.sh /run.sh
COPY       ./email/opendkim.sh /opendkim.sh
COPY       ./email/cert /etc/postfix/cert
RUN        chmod +x /run.sh /opendkim.sh

# Set up volumes
VOLUME     [ "/var/spool/postfix", "/etc/postfix", "/etc/opendkim/keys" ]

# Run supervisord
USER       root
WORKDIR    /root

EXPOSE     587 25
CMD        ["/bin/sh", "-c", "/run.sh"]
