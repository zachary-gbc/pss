#!/bin/bash

sleep 30

. /var/www/html/pss/conf/pss.conf

mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
lanip=$(hostname -I)
dbhostname=$(curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=init&device=$mac")

if [[ $dbhostname == "new" ]]
then
  bash /home/pi/scripts/pushover.sh "New Device Connected" "bugle" "Device: $HOSTNAME @ $lanip"
fi

if [[ ${#dbhostname} < 4 ]]
then
  exit 1
fi

if [[ ${#dbhostname} > 20 ]]
then
  exit 1
fi

if [[ -f "/home/pi/initstep2" ]]
then
  if [[ $HOSTNAME != $dbhostname ]]
  then
    bash /home/pi/scripts/pushover.sh "Hostname Did Not Set" "tugboat" "$HOSTNAME - Received: $dbhostname"
    exit 1
  fi

  rm /home/pi/initstep2
  bash /home/pi/scripts/pushover.sh "Initialized New Device" "tugboat" "$HOSTNAME"
  exit 1
fi

if [[ $HOSTNAME != $dbhostname ]]
then
  sudo raspi-config nonint do_hostname $dbhostname
  sleep 10
  echo "true" > /home/pi/initstep2
  sudo reboot now
fi
  