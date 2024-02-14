#!/bin/bash

sleep 45
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting devicedetails" >> /home/pi/log/$log.log

. /var/www/html/pss/conf/pss.conf

log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=devicedetails&device=$mac&devname=$HOSTNAME" >> /home/pi/log/$log.log