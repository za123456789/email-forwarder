#!/bin/sh

## Send the user usefull info in the notification email
FROM="`awk '/^[Ff][Rr][Oo][Mm]:/' $EMAIL`"
SUBJECT="`awk '/[Ss][Uu][Bb][Jj][Ee][Cc][Tt]:/' $EMAIL`"
## Logfile to log status to
LOGFILE="/tmp/virus_action.log"
## From email address where the notification email comes from
MAILFROM=""
## Email address to send a copy of the virus/spam email to
QUARANTAINE=""

exec 1>>"$LOGFILE"
exec 2>>"$LOGFILE"

notify_user() {
         echo "HELO `hostname -f`"
         echo "MAIL FROM:<$MAILFROM>"
         for RCPT in "$RECIPIENTS"; do
         echo "RCPT TO:<$RCPT>"
         done
         echo "RCPT TO:<$RECIPIENTS>"
         echo "DATA"
         sleep 1
         echo "Subject: Mail system notification"
         echo "Dear user,"
         echo ""
         echo "A virus or spam email has been found by the mail system."
         echo "We have gathered the following information for you:"
         echo ""
         echo "Date: `date`"
         echo "$FROM"
         echo "$SUBJECT"
         echo "Virus/spam name: $VIRUS"
         echo ""
         echo "If you think this is incorrect, please notify your administrator at once."
         echo ""
         echo "Best regards,"
         echo ""
         echo "Your mail administrator"
         echo "."
         echo "quit"
}

send_virus() {
         echo "HELO `hostname -f`"
         echo "MAIL FROM:<$MAILFROM>"
         echo "RCPT TO:<$QUARANTAINE>"
         echo "DATA"
         sleep 1
         cat "$EMAIL"
         echo "."
         echo "quit"
}

notify_user | nc localhost 10026
send_virus | nc localhost 10026
rm "$EMAIL"
