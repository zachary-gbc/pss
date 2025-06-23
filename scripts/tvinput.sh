#!/usr/bin/bash

# 1 = input (I-1, I-2, ... I-5) echo 'tx 4F:82:10:00 $tv' | cec-client -s -d 1 for input 1, change the 10 to 20 for input 2

. /var/www/html/pss/conf/pss.conf
datetime=$(date '+%Y-%m-%d %H:%M:%S');
log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
input="0"

echo "MESSAGE $datetime: TV Input: $1" >> /home/pi/log/$log.log

if [[ ! -z $1 ]]
then
  if [[ $1 == "IS-1" ]] || [[ $1 == "IE-1" ]]
  then
    echo tx 4F:82:10:00 $tv | cec-client -s -d 1
    echo "MESSAGE $datetime: TV Turned to Input 1" >> /home/pi/log/$log.log
    input="1"
  fi
  if [[ $1 == "IS-2" ]] || [[ $1 == "IE-2" ]]
  then
    echo tx 4F:82:20:00 $tv | cec-client -s -d 1
    echo "MESSAGE $datetime: TV Turned to Input 2" >> /home/pi/log/$log.log
    input="2"
  fi
  if [[ $1 == "IS-3" ]] || [[ $1 == "IE-3" ]]
  then
    echo tx 4F:82:30:00 $tv | cec-client -s -d 1
    echo "MESSAGE $datetime: TV Turned to Input 3" >> /home/pi/log/$log.log
    input="3"
  fi
  if [[ $1 == "IS-4" ]] || [[ $1 == "IE-4" ]]
  then
    echo tx 4F:82:40:00 $tv | cec-client -s -d 1
    echo "MESSAGE $datetime: TV Turned to Input 4" >> /home/pi/log/$log.log
    input="4"
  fi
  if [[ $1 == "IS-5" ]] || [[ $1 == "IE-5" ]]
  then
    echo tx 4F:82:50:00 $tv | cec-client -s -d 1
    echo "MESSAGE $datetime: TV Turned to Input 5" >> /home/pi/log/$log.log
    input="5"
  fi
fi
sleep 1

if [[ $input != "0" ]]
then
  curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&input=$input" >> /home/pi/log/$log.log
fi
