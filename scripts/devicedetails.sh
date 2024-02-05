#!/bin/bash

sleep 30

. /var/www/html/pss/conf/pss.conf

mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=devicedetails&device=$mac&devname=$HOSTNAME"