#!/usr/bin/bash

. /var/www/html/pss/conf/pss.conf
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
lanip=$(hostname -I | tr -d ' ')
pssonoff="off"
pssonoff=$(</home/pi/pssonoff)
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');

if [ ${pssonoff:0:3} == "off" ]
then
  echo "MESSAGE $datetime: Starting cronsandmirror" >> /home/pi/log/$log.log
  sudo curl -Ss http://$database_ip/pss/scripts/createschedule.php?device=$mac --output /etc/cron.d/loopschedule
  if [ "$database_ip" != "$lanip" ]
  then
    sudo wget -np -nH --cut-dirs 2 -mirror -R '*index*' -P /var/www/html/pss/files/ http://$database_ip/pss/files/
    sudo chown www-data:www-data /var/www/html/pss/files/*
  fi
  sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=cronsandmirror&device=$mac" >> /home/pi/log/$log.log
fi

if [ $1 == "manualcrons" ]
then
  echo "MESSAGE $datetime: Starting cronsandmirror(manualcrons)" >> /home/pi/log/$log.log
  sudo curl -Ss http://$database_ip/pss/scripts/createschedule.php?device=$mac --output /etc/cron.d/loopschedule
fi
