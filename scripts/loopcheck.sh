#!/usr/bin/bash

. /var/www/html/pss/conf/pss.conf

pssonoff="off"
pssonoff=$(</home/pi/pssonoff)
dow=$(date +%u)
datetime=$(date '+%Y-%m-%d %H:%M:%S')
log=$(date -I)

if [[ ${pssonoff:0:3} == "off" ]]
then
  exit 1
fi

type=${pssonoff:0:1}
number=${pssonoff:2}

if [[ $type == "L" ]]
then
  if [[ $omxorvlc == "o" ]]
  then
    file="/var/www/html/pss/files/loop-$number.mp4"
  else
    file="/var/www/html/pss/files/loop-$number.m3u"
  fi
  message="Loop"
else
  file="/var/www/html/pss/files/$number.mp4"
  message="Graphic"
fi

if [[ $omxorvlc == "o" ]]
then
  omxrunning=$(pidof omxplayer.bin)
  if [[ -z $omxrunning ]]
  then
    omxplayer --no-keys --loop $file &
    sleep 5
    omxrunning=$(pidof omxplayer.bin)
    if [[ -z $omxrunning ]]
    then
      echo "ALERT $datetime: Loop Stopped" >> /home/pi/log/$log.log
      bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "Loop Stopped"
      exit 1
    fi
  fi
else
  vlcrunning=$(pidof vlc)
  if [[ -z $vlcrunning ]]
  then
    DISPLAY=:0 cvlc --no-audio --fullscreen --no-video-title-show --loop --quiet $file &
    sleep 5
    vlcrunning=$(pidof vlc)
    if [[ -z $vlcrunning ]]
    then
      echo "ALERT $datetime: Loop Stopped" >> /home/pi/log/$log.log
      bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "Loop Stopped"
      exit 1
    fi
  fi
fi

sleep 300
bash /home/pi/scripts/loopcheck.sh &