#!/bin/bash

. /var/www/html/pss/conf/pss.conf

lanip=$(hostname -I | tr -d ' ')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');

if [ "$database_ip" == "$lanip" ]
then
  echo "MESSAGE $datetime: Starting loopcreate" >> /home/pi/log/$log.log
  sudo curl -Ss http://$database_ip/pss/scripts/createloop.php >> /home/pi/log/$log.log
fi
