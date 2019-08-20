FROM alpine:latest

RUN        true && \
           apk add --no-cache --upgrade cyrus-sasl cyrus-sasl-plain cyrus-sasl-login && \
           apk add --no-cache postfix 			\
           					  opendkim 			        \
           					  bash 				          \
           					  clamav 			          \
           					  clamav-libunrar 	    \
                      clamsmtp              \           
           					  ca-certificates 	    \
           					  tzdata 			          \
           					  supervisor 		        \
           					  rsyslog 		       && \	
           apk add --no-cache --upgrade musl musl-utils && \
           (rm "/tmp/"* 2>/dev/null || true) && (rm -rf /var/cache/apk/* 2>/dev/null || true)

RUN        addgroup spamassassin && \        
           adduser -S -D -G spamassassin -h /var/lib/spamassassin/ spamassassin && \
           apk add --no-cache razor spamassassin 

RUN        mkdir /run/clamav && \
           chown clamav:clamav /run/clamav && \
           chmod 750 /run/clamav

RUN        sed -i 's/^#Foreground/Foreground/g' /etc/clamav/clamd.conf && \
           sed -i 's/^#ScanMail yes/ScanMail yes/g' /etc/clamav/clamd.conf && \
           echo "TCPSocket 3310" >> /etc/clamav/clamd.conf 

RUN        sa-update && \
           freshclam

# Set up configuration

COPY       ./email/supervisord.conf /etc/supervisord.conf
COPY       ./email/rsyslog.conf /etc/rsyslog.conf
COPY       ./email/opendkim.conf /etc/opendkim/opendkim.conf
COPY       ./email/run.sh /run.sh
COPY       ./email/opendkim.sh /opendkim.sh
COPY       ./email/cert /etc/postfix/cert

# clamsmtp 
COPY       ./email/clamsmtp.txt /clamsmtp.txt
COPY       ./email/scripts /etc/postfix/scripts

RUN        chmod +x /run.sh /opendkim.sh /etc/postfix/scripts/virus_action.sh && \
           chown -R clamav:clamav /etc/postfix/scripts && \
           cat /clamsmtp.txt >> /etc/postfix/master.cf 

RUN        sed -i 's/^#Listen\: 0.0.0.0\:10025/Listen\: 127.0.0.1\:10025/g' /etc/clamsmtpd.conf && \
           sed -i 's/^#TempDirectory\: \/tmp/TempDirectory\: \/tmp/g' /etc/clamsmtpd.conf && \
           sed -i 's/^#Action\: drop/Action\: drop/g' /etc/clamsmtpd.conf && \
           sed -i 's/^#Quarantine\: off/Quarantine\: on/g' /etc/clamsmtpd.conf && \
           echo "VirusAction: /etc/postfix/scripts/virus_action.sh" >> /etc/clamsmtpd.conf

RUN        echo "content_filter = scan:127.0.0.1:10025" >> /etc/postfix/main.cf  && \
           echo "receive_override_options = no_address_mappings" >> /etc/postfix/main.cf

# Set up volumes
VOLUME     [ "/var/spool/postfix", "/etc/postfix", "/etc/opendkim/keys" ]

# Run supervisord
USER       root
WORKDIR    /root

EXPOSE     587 25
CMD        ["/bin/sh", "-c", "/run.sh"]
