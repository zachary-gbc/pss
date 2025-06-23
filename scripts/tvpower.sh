#!/usr/bin/bash

# 1 = power (on=N-1/off=F-1)

. /var/www/html/pss/conf/pss.conf
datetime=$(date '+%Y-%m-%d %H:%M:%S');
log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

echo "MESSAGE $datetime: TV Power: $1" >> /home/pi/log/$log.log

if [[ ! -z $1 ]]
then
  if [[ $1 == "PS-1" ]]
  then
    echo on 0 | cec-client -s -d 1
    sleep 10
    powerstatus=$(echo pow 0 | cec-client -s -d 1)
    if [[ $powerstatus != *": on"* ]]
    then
      echo on 0 | cec-client -s -d 1
      sleep 10
      powerstatus=$(echo pow 0 | cec-client -s -d 1)
      if [[ $powerstatus != *": on"* ]]
      then
        echo "ALERT $datetime: TV Did Not Turn On" >> /home/pi/log/$log.log
        bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "TV Did Not Turn On"
      fi
    fi
  fi
fi

if [[ ! -z $1 ]]
then
  if [[ $1 == "PE-1" ]]
  then
    echo standby 0 | cec-client -s -d 1
    sleep 10
    powerstatus=$(echo pow 0 | cec-client -s -d 1)
    if [[ $powerstatus != *"standby"* ]]
    then
      echo standby 0 | cec-client -s -d 1
      sleep 10
      powerstatus=$(echo pow 0 | cec-client -s -d 1)
      if [[ $powerstatus != *"standby"* ]]
      then
        echo "ALERT $datetime: TV Did Not Turn Off" >> /home/pi/log/$log.log
        bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "TV Did Not Turn Off"
      fi
    fi
  fi
fi

powerstatus=$(echo pow 0 | cec-client -s -d 1)
if [[ $powerstatus == *"standby"* ]]
then
  power="Off"
elif  [[ $powerstatus == *": on"* ]]
then
  power="On"
else
  power="Unknown"
fi

curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&power=$power" >> /home/pi/log/$log.log
