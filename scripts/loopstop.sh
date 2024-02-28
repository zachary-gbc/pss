#!/usr/bin/bash

# 1 = screen off (yes=1/no=0)
# 2 = screen input change (value)   echo 'tx 4F:82:10:00 $tv' | cec-client -s -d 1 for input 1, change the 10 to 20 for input 2

. /var/www/html/pss/conf/pss.conf

tv="0"
power="Off"
dow=$(date +%u)
input="unknown"
datetime=$(date '+%Y-%m-%d %H:%M:%S');
log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

echo "MESSAGE $datetime: Stopping Loop/Graphic, Turn TV On (1=yes): $1, Input Set: $2" >> /home/pi/log/$log.log

echo "off" > /home/pi/pssonoff
sleep 20

if [[ $omxorvlc == "o" ]]
then
  pkill omxplayer
else
  pkill vlc
fi
sleep 5

if [[ $omxorvlc == "o" ]]
then
  omxrunning=$(pidof omxplayer.bin)
  if [[ $omxrunning ]]
  then
    pkill omxplayer
    sleep 5
    omxrunning=$(pidof omxplayer.bin)
    if [[ $omxrunning ]]
    then
      echo "ALERT $datetime: omxplayer Failed to Stop" >> /home/pi/log/$log.log
      bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "Player Failed to Stop"
    fi
  fi
else
  vlcrunning=$(pidof vlc.bin)
  if [[ $vlcrunning ]]
  then
    pkill vlc
    sleep 5
    vlcrunning=$(pidof vlc.bin)
    if [[ $vlcrunning ]]
    then
      echo "ALERT $datetime: vlc Failed to Stop" >> /home/pi/log/$log.log
      bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "Player Failed to Stop"
    fi
  fi
fi
sleep 5

if [[ $2 == "1" ]]
then
  echo tx 4F:82:10:00 $tv | cec-client -s -d 1
  sleep 5
  input="1"
fi
if [[ $2 == "2" ]]
then
  echo tx 4F:82:20:00 $tv | cec-client -s -d 1
  sleep 5
  input="2"
fi
if [[ $2 == "3" ]]
then
  echo tx 4F:82:30:00 $tv | cec-client -s -d 1
  sleep 5
  input="3"
fi
if [[ $1 == "1" ]]
then
  echo standby $tv | cec-client -s -d 1
  sleep 10
  powerstatus=$(echo pow $tv | cec-client -s -d 1)
  if [[ $powerstatus != *"standby"* ]]
  then
    echo standby $tv | cec-client -s -d 1
    sleep 10
    powerstatus=$(echo pow $tv | cec-client -s -d 1)
    if [[ $powerstatus != *"standby"* ]]
    then
      echo "ALERT $datetime: TV Failed to Turn Off" >> /home/pi/log/$log.log
      bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "TV Failed to Turn Off"
      sleep 5
      power="On"
    fi
  fi
fi

sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&power=$power&input=$input&loop=0" >> /home/pi/log/$log.log