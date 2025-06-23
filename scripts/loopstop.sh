#!/usr/bin/bash

# 1 = minutes (S-minutes)
# 2 = power (yes=F-3/no=F-4)

. /var/www/html/pss/conf/pss.conf
dow=$(date +%u)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
power=""
vars=""

if [[ ! -z $1 ]]; then vars+="$1 "; fi
if [[ ! -z $2 ]]; then vars+="$2 "; fi
if [[ ! -z $3 ]]; then vars+="$3 "; fi
if [[ ! -z $4 ]]; then vars+="$4 "; fi
if [[ ! -z $5 ]]; then vars+="$5 "; fi
if [[ ! -z $6 ]]; then vars+="$6 "; fi

echo "MESSAGE $datetime: Stopping Loop/Graphic ($vars)" >> /home/pi/log/$log.log

function checkvariable {
  vartype=${1:0:2}
  case $vartype in
    "PE") power=$1 ;;
    "DM")
      if [[ $1 == "DM-0" ]]; then exit 1; fi
      minutes=${1:3}
      seconds=$((minutes * 60))
      sleep $seconds
      ;;
  esac
}

if [[ ! -z $1 ]]; then checkvariable "$1"; fi
if [[ ! -z $2 ]]; then checkvariable "$2"; fi
if [[ ! -z $3 ]]; then checkvariable "$3"; fi
if [[ ! -z $4 ]]; then checkvariable "$4"; fi
if [[ ! -z $5 ]]; then checkvariable "$5"; fi
if [[ ! -z $6 ]]; then checkvariable "$6"; fi

echo "off" > /home/pi/pssonoff
sleep 20

if [[ $omxorvlc == "o" ]]
then
  pkill omxplayer
else
  pkill vlc
fi
sleep 60

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

if [[ $power != "" ]]; then bash /home/pi/scripts/tvpower.sh $power; fi

sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&loop=0" >> /home/pi/log/$log.log
