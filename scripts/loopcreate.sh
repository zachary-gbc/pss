#!/bin/bash

. /var/www/html/pss/conf/pss.conf

lanip=$(hostname -I)

if [ $database_ip == $lanip ]
then
  sudo curl -Ss http://$database_ip/pss/scripts/createloops.php >> /home/pi/log/$log.log
fi
