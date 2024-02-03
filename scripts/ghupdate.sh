#!/bin/bash

. /home/pi/pss.conf

sleep 60

lastupdate=$(</home/pi/lastupdatecommit)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

sudo rm -r -f /home/pi/pss
git clone --depth=1 https://github.com/zachary-gbc/pss /home/pi/pss
cd /home/pi/pss
lastcommit=$(git log --pretty=format:"%H")

if [[ $lastcommit != $lastupdate ]]
then
  find . -name '*DS_Store*' -delete

  # Scripts
  sudo mv -f /home/pi/pss/scripts/* /home/pi/scripts/

  # Crons
  sudo mv -f /home/pi/pss/crons/general /etc/cron.d/general
  sudo chown root:root /etc/cron.d/general
  sudo mv -f /home/pi/pss/crons/screens /etc/cron.d/screens
  sudo chown root:root /etc/cron.d/screens

  # Website
  sudo rsync -avu "/home/pi/pss/website/" "/var/www/html"

  echo $lastcommit > /home/pi/lastupdatecommit

  curl http://$database_ip/pss/scripts/dbupdate.php?type=ghupdate\&device=$mac
fi

sudo rm -r -f /home/pi/pss
