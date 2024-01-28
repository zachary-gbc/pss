#!/usr/bin/bash

pssonoff="off"
pssonoff=$(</home/pi/looponoff)
dow=$(date +%u)
datetime=$(date '+%Y-%m-%d %H:%M:%S')
log=$(date -I)

if [[ ${looponoff:0:3} == "off" ]]
then
  exit 1
fi

type=${looponoff:0:1}
number=${looponoff:2}

if [[ $type == "M" ]]
then
  file="/var/www/html/pss/files/loop-$number.m3u"
else
  file="/var/www/html/pss/files/$number.mp4"
fi

vlcrunning=$(pidof vlc.bin)
if [[ -z $vlcrunning ]]
then
  cvlc --no-audio --fullscreen --no-video-title-show --loop --quiet $file &
  sleep 5
  vlcrunning=$(pidof vlc.bin)
  if [[ -z $vlcrunning ]]
  then
    echo "ALERT $datetime: Loop Stopped" >> /home/pi/log/$log.log
    bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "Loop Stopped"
    exit 1
  fi
fi

sleep 60
bash /home/pi/scripts/loopcheck.sh &