#!/bin/sh

echo -e "******************************"
echo -e "**** POSTFIX STARTING UP *****"
echo -e "******************************"


# Make and reown postfix folders
mkdir -p /var/spool/postfix/ && mkdir -p /var/spool/postfix/pid
chown root: /var/spool/postfix/
chown root: /var/spool/postfix/pid

# Disable SMTPUTF8, because libraries (ICU) are missing in alpine
postconf -e "smtputf8_enable = no"

# Update aliases database. It's not used, but postfix complains if the .db file is missing
postalias /etc/postfix/aliases

# Don't relay for any domains
postconf -e "relay_domains = hash:/etc/postfix/relaydomains"

# Increase the allowed header size, the default (102400) is quite smallish
postconf -e "header_size_limit = 4096000"

postconf -e "message_size_limit = 50000000"

postconf -e "smtpd_sasl_auth_enable = yes"

postconf -e "smtpd_sasl_security_options = noanonymous"

postconf -e "disable_vrfy_command = yes"

postconf -e "smtpd_recipient_restrictions = reject_invalid_hostname, reject_non_fqdn_hostname, reject_non_fqdn_sender, reject_non_fqdn_recipient, reject_unknown_sender_domain, reject_unknown_recipient_domain, permit_sasl_authenticated, permit_mynetworks, reject_unauth_destination, permit"

postconf -e "smtpd_tls_security_level=may"

# Only offer SASL in a TLS session                                           
postconf -e "smtpd_tls_auth_only = no"

# Public Certificate                                                         
postconf -e "smtpd_tls_cert_file = /etc/postfix/cert/smtp.cert"                  
postconf -e "smtpd_tls_eccert_file = /etc/postfix/cert/smtp.ec.cert"

# Private Key (without passphrase)                                           
postconf -e "smtpd_tls_key_file = /etc/postfix/cert/smtp.key"                    
postconf -e "smtpd_tls_eckey_file = /etc/postfix/cert/smtp.ec.key"

postconf -e "tls_random_source = dev:/dev/urandom"

# TLS related logging (set to 2 for debugging)                     
postconf -e "smtpd_tls_loglevel = 0"

# Avoid Denial-Of-Service-Attacks                                            
postconf -e "smtpd_client_new_tls_session_rate_limit = 10"

# Activate TLS Session Cache                                       
postconf -e "smtpd_tls_session_cache_database = btree:/var/lib/postfix/smtpd_session_cache"

# Deny some TLS-Ciphers                                            
postconf -e "smtpd_tls_exclude_ciphers = EXP EDH-RSA-DES-CBC-SHA ADH-DES-CBC-SHA DES-CBC-SHA SEED-SHA"

postconf -e "smtp_tls_security_level = may"

postconf -e "smtpd_delay_reject=yes"
postconf -e "smtpd_helo_required=yes"
postconf -e "smtpd_helo_restrictions=permit_mynetworks,reject_invalid_helo_hostname,permit"
postconf -e "smtpd_sender_restrictions=permit_mynetworks"

postconf -e "myhostname = test.com"

postconf -e "virtual_alias_domains = test.com"
postconf -e "virtual_alias_maps = hash:/etc/postfix/virtual"

postconf -e "smtpd_banner = \$myhostname ESMTP "

sed -i -r -e 's/^#submission/submission/' /etc/postfix/master.cf
postmap /etc/postfix/relaydomains
postmap /etc/postfix/virtual

exec supervisord -c /etc/supervisord.conf
