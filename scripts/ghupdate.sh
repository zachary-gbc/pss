#!/bin/bash

sleep 62

. /var/www/html/pss/conf/pss.conf
lastupdate=$(</home/pi/pss_lastupdatecommit)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting ghupdate" >> /home/pi/log/$log.log

sudo rm -r -f /home/pi/pss
git clone --depth=1 https://github.com/zachary-gbc/pss /home/pi/pss
cd /home/pi/pss
lastcommit=$(git log --pretty=format:"%H")

if [[ $lastcommit != $lastupdate ]]
then
  find . -name '*DS_Store*' -delete
  mv /home/pi/pss/scripts/ghupdate.sh /home/pi/ghupdate.sh
  ( sleep 60; mv /home/pi/ghupdate.sh /home/pi/scripts/ghupdate.sh ) & 

  # Scripts
  sudo mv -f /home/pi/pss/scripts/* /home/pi/scripts/

  # Crons
  sudo mv -f /home/pi/pss/crons/pss /etc/cron.d/pss
  sudo chown root:root /etc/cron.d/pss

  # Website
  sudo rsync -avu "/home/pi/pss/website/" "/var/www/html"
  sudo chown www-data:www-data /var/www/html/pss/scripts/manualaction

  echo $lastcommit > /home/pi/pss_lastupdatecommit
fi

curl http://$database_ip/pss/scripts/dbupdate.php?type=ghupdate\&device=$mac
sudo rm -r -f /home/pi/pss
