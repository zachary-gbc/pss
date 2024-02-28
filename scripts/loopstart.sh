#!/usr/bin/bash

# 1 = loop (L-#) or graphic (G-#-L or G-#-P)
# 2 = start screen on (yes=1/no=0)
# 3 = start screen input change (value)   echo 'tx 4F:82:10:00 $tv' | cec-client -s -d 1 for input 1, change the 10 to 20 for input 2
# bash /home/pi/scripts/loopstart.sh L-1 1 1
# bash /home/pi/scripts/loopstart.sh G-1-P 1 1

. /var/www/html/pss/conf/pss.conf

tv="0"
power="On"
input="unknown"
datetime=$(date '+%Y-%m-%d %H:%M:%S');
log=$(date -I)
type=${1:0:1}
number=${1:2}
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

echo "MESSAGE $datetime: Starting $1, Turn TV On (1=yes): $2, Input Set: $3" >> /home/pi/log/$log.log

if [ $type == "L" ]
then
  file="/var/www/html/pss/files/loop-$number.m3u"
  downloadlink="http://$database_ip/pss/files/loop-$number.m3u"
  message="Loop"
else
  file="/var/www/html/pss/files/$number.mp4"
  downloadlink="http://$database_ip/pss/files/$number.mp4"
  message="Graphic"
fi

if [[ $1 == 0 ]]
then
  bash /home/pi/scripts/loopstop.sh $2 $3
  exit 1
fi

if [[ $1 != 1 ]] && [[ ! -f "$file" ]]
then
  curl -Ss "$downloadlink" > /home/pi/download.file
  sudo mv /home/pi/download.file $file
  if [[ ! -f "$file" ]]
  then
    echo "ALERT $datetime: File Not Available" >> /home/pi/log/$log.log
    bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "$message Not Available"
    exit 1
  fi
fi

if [[ $2 == "1" ]]
then
  echo on $tv | cec-client -s -d 1
  sleep 10
  powerstatus=$(echo pow $tv | cec-client -s -d 1)
  if [[ $powerstatus != *": on"* ]]
  then
    echo on $tv | cec-client -s -d 1
    sleep 10
    powerstatus=$(echo pow $tv | cec-client -s -d 1)
    if [[ $powerstatus != *": on"* ]]
    then
      echo "ALERT $datetime: TV Did Not Turn On" >> /home/pi/log/$log.log
      bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "TV Did Not Turn On"
      power="Off"
    fi
  fi
fi

if [[ $3 == 1 ]]
then
  sleep 10
  echo tx 4F:82:10:00 $tv | cec-client -s -d 1
  echo "MESSAGE $datetime: TV Turned to Input 1" >> /home/pi/log/$log.log
  input="1"
fi
if [[ $3 == 2 ]]
then
  sleep 10
  echo tx 4F:82:20:00 $tv | cec-client -s -d 1
  echo "MESSAGE $datetime: TV Turned to Input 2" >> /home/pi/log/$log.log
  input="2"
fi
if [[ $3 == 3 ]]
then
  sleep 10
  echo tx 4F:82:30:00 $tv | cec-client -s -d 1
  echo "MESSAGE $datetime: TV Turned to Input 3" >> /home/pi/log/$log.log
  input="3"
fi

pkill loopcheck.sh
if [[ $omxorvlc == "o" ]]
then
  pkill omxplayer
else
  pkill vlc
fi
sleep 1

if [[ $1 != 1 ]]
then
  echo "MESSAGE $datetime: Starting $message" >> /home/pi/log/$log.log
  if [[ $omxorvlc == "o" ]]
  then
    bash omxloop.sh $message $file &
  else
    DISPLAY=:0 cvlc --no-audio --fullscreen --no-video-title-show --loop --quiet $file &
  fi
  echo "$1" > /home/pi/pssonoff
fi
curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&power=$power&input=$input&loop=$1" >> /home/pi/log/$log.log

sleep 5
bash /home/pi/scripts/loopcheck.sh &