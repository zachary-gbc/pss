#!/usr/bin/bash

# loop (L-#) or graphic (G-#-L or G-#-P)
# power (start: yes=PS-1/no=PS-0, end: yes=PE-1/no=PE-0)
# input (start: IS-1, end: IE-1)
# duration in minutes (DM-minutes)
# bash /home/pi/scripts/loopstart.sh L-1 1 1
# bash /home/pi/scripts/loopstart.sh G-1-P 1 1

. /var/www/html/pss/conf/pss.conf
datetime=$(date '+%Y-%m-%d %H:%M:%S');
log=$(date -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
type=""
number=""
vars=""
loopgraphic="off"

if [[ ! -z $1 ]]; then vars+="$1 "; fi
if [[ ! -z $2 ]]; then vars+="$2 "; fi
if [[ ! -z $3 ]]; then vars+="$3 "; fi
if [[ ! -z $4 ]]; then vars+="$4 "; fi
if [[ ! -z $5 ]]; then vars+="$5 "; fi
if [[ ! -z $6 ]]; then vars+="$6 "; fi

echo "MESSAGE $datetime: Loop Start ($vars)" >> /home/pi/log/$log.log

function checkvariable {
  vartype=${1:0:2}
  case $vartype in
    "L-"|"G-") type=${1:0:1}; number=${1:2}; loopgraphic=$1 ;;
    "PS") bash /home/pi/scripts/tvpower.sh $1 ;;
    "IS") bash /home/pi/scripts/tvinput.sh $1 ;;
    "DM")
      if [[ $1 != "DM-0" ]]
      then
        minutes=${1:3}
        echo "/bin/bash /home/pi/scripts/loopstop.sh $vars &" | at now + $minutes minutes
      fi
  esac
}

if [[ ! -z $1 ]]; then checkvariable "$1"; fi
if [[ ! -z $2 ]]; then checkvariable "$2"; fi
if [[ ! -z $3 ]]; then checkvariable "$3"; fi
if [[ ! -z $4 ]]; then checkvariable "$4"; fi
if [[ ! -z $5 ]]; then checkvariable "$5"; fi
if [[ ! -z $6 ]]; then checkvariable "$6"; fi

if [[ $type == "L" ]]
then
  if [[ $omxorvlc == "o" ]]
  then
    file="/var/www/html/pss/files/loop-$number.mp4"
    downloadlink="http://$database_ip/pss/files/loop-$number.mp4"
  else
    file="/var/www/html/pss/files/loop-$number.m3u"
    downloadlink="http://$database_ip/pss/files/loop-$number.m3u"
  fi
  message="Loop"
else
  file="/var/www/html/pss/files/$number.mp4"
  downloadlink="http://$database_ip/pss/files/$number.mp4"
  message="Graphic"
fi

if [[ $loopgraphic != "L-0" ]] && [[ ! -f "$file" ]]
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

pkill loopcheck.sh
if [[ $omxorvlc == "o" ]]
then
  pkill omxplayer
else
  pkill vlc
fi
sleep 1

if [[ $loopgraphic != "L-0" ]]
then
  echo "MESSAGE $datetime: Starting $message ($loopgraphic)" >> /home/pi/log/$log.log
  if [[ $omxorvlc == "o" ]]
  then
    omxplayer --no-keys --loop $file &
  else
    DISPLAY=:0 cvlc --no-audio --fullscreen --no-video-title-show --loop --quiet $file &
  fi
  echo "$loopgraphic" > /home/pi/pssonoff
  curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&loop=$loopgraphic" >> /home/pi/log/$log.log

  sleep 5
  bash /home/pi/scripts/loopcheck.sh &
fi
