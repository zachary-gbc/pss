#!/bin/bash

. /var/www/html/pss/conf/pss.conf
title=$1
sound=$2
message=$3
log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')


if [[ $pushover_configured == "yes" ]]
then
  echo "MESSAGE $datetime: Starting pushover" >> /home/pi/log/$log.log
  response=$(curl -s --form-string "token=$pushover_token" --form-string "user=$pushover_user_key" --form-string "message=$message" --form-string "title=$title" --form-string "sound=$sound" https://api.pushover.net/1/messages.json)

  if [[ "$response" == *"\"status\":1"* ]]; then
    sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=pushover&device=$mac&title=$title&response=true" >> /home/pi/log/$log.log
  else
    sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=pushover&device=$mac&title=$title&response=false" >> /home/pi/log/$log.log
  fi
fi
