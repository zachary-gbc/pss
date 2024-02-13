#!/bin/bash

. /var/www/html/pss/conf/pss.conf

lanip=$(hostname -I)
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting loopcreate" >> /home/pi/log/$log.log

if [ "$database_ip" == "$lanip" ]
then
  sudo curl -Ss http://$database_ip/pss/scripts/createloop.php >> /home/pi/log/$log.log
fi
