#!/bin/bash

# Task Format: <number>-<variable>
# Numbers:
#   11 - Start Loop
#   12 - Stop Loop
#   13 - Turn TV On
#   14 - Turn TV Off
#   15 - Download Graphic or Loop
#   16 - Download Crons
#   21 - Change TV to Input 1
#   22 - Change TV to Input 2
#   23 - Change TV to Input 3
#   24 - Change TV to Input 4
#   25 - Change TV to Input 5

. /var/www/html/pss/conf/pss.conf

x=1
vars=""
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
changes=$(curl http://$database_ip/pss/scripts/dbupdate.php?type=manualaction\&device=$mac)

if [[ "$changes" == "null" ]]
then
  exit 1
fi

while IFS= read -r line; do
  number=${line:0:2}
  variables=${line:3}
  lgtype=${variables:0:1}
  if [[ "$lgtype" == "L" ]]; then length="${#variables}"; variables="${variables:0:length-2}"; fi
  echo "MESSAGE $datetime: Starting Manual Action ($number-$variables)" >> /home/pi/log/$log.log

  case $number in
    11) bash /home/pi/scripts/loopstart.sh $variables ;;
    12) bash /home/pi/scripts/loopstop.sh ;;
    13) echo on 0 | cec-client -s -d 1; sleep 10; vars="power=On" ;;
    14) echo standby 0 | cec-client -s -d 1; vars="power=Off" ;;
    15) sudo curl -o /var/www/html/pss/files/$variables.mp4 http://$database_ip/pss/files/$2.mp4 ;;
    16) bash /home/pi/scripts/cronsandmirror.sh manualcrons ;;
    21) echo tx 4F:82:10:00 0 | cec-client -s -d 1; vars="input=1" ;;
    22) echo tx 4F:82:20:00 0 | cec-client -s -d 1; vars="input=2" ;;
    23) echo tx 4F:82:30:00 0 | cec-client -s -d 1; vars="input=3" ;;
    24) echo tx 4F:82:40:00 0 | cec-client -s -d 1; vars="input=4" ;;
    25) echo tx 4F:82:50:00 0 | cec-client -s -d 1; vars="input=5" ;;
    *) ;;
  esac

  curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=locationstatus&device=$mac&$vars" >> /home/pi/log/$log.log

  sleep 1
done <<< "$changes"
