#!/usr/bin/bash

. /var/www/html/pss/conf/pss.conf
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
power="Off"
log=$(date -I)
pssonoff=$(</home/pi/pssonoff)
type=${pssonoff:0:1}
number=${pssonoff:2}

powerstatus=$(echo pow 0 | cec-client -s -d 1)
if [[ $powerstatus == *": on"* ]]
then
  power="On"
fi

if [[ $pssonoff == "off" ]]
then
  loop=0
else
  type=${pssonoff:0:1}
  number=${pssonoff:2}
  loop="$type-$number"
fi

sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&power=$power&loop=$loop" >> /home/pi/log/$log.log