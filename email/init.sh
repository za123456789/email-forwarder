#!/bin/bash

SECRETS=/etc/postsrsd/postsrsd.secret

if [[ ! -f $SECRETS ]]; then
   echo "provide $SECRETS via volume or bind mount"
   exit 1
fi
 
/usr/sbin/postsrsd -d mydevops.space -s $SECRETS -l 0.0.0.0 -u postsrsd
