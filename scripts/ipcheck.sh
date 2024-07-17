#!/bin/bash

. /var/www/html/pss/conf/pss.conf

if [[ ! -f /home/pi/scripts/lanip ]]
then
  echo "1.1.1.1" > /home/pi/scripts/lanip
  echo "1.1.1.1" > /home/pi/scripts/wanip
fi

mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
curlan=$(cat /home/pi/scripts/lanip)
curwan=$(cat /home/pi/scripts/wanip)
lanip=$(hostname -I)
wanip=$(curl https://ipecho.net/plain)
laniplength=${#lanip}
waniplength=${#wanip}
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting ipcheck" >> /home/pi/log/$log.log

if [ $waniplength > 5 ] && [ $laniplength > 5 ]
then
  if [ $curwan != $wanip ] || [ $curlan != $lanip ]
  then
    if [[ $pushover_configured == "yes" ]] && [[ $alert_on_ip_change == "yes" ]]
    then
      bash /home/pi/scripts/pushover.sh "$HOSTNAME IP Changed" "none" "WAN: $wanip | LAN: $lanip"
    fi
    echo $lanip > /home/pi/scripts/lanip
    echo $wanip > /home/pi/scripts/wanip
    curl http://$database_ip/pss/scripts/dbupdate.php?type=ipchange\&device=$mac\&ipaddress=$lanip
  fi
fi