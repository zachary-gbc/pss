#!/bin/bash

# Task Format: <number>-<variable>
# Numbers:
#   11 - Start Loop
#   12 - Stop Loop
#   13 - Turn TV On
#   14 - Turn TV Off
#   15 - Download Graphics and Loops
#   16 - Download Crons
#   21 - Change TV to Input 1
#   22 - Change TV to Input 2
#   23 - Change TV to Input 3
#   24 - Change TV to Input 4
#   25 - Change TV to Input 5

. /var/www/conf/csdb.conf
. /var/www/conf/pss.conf
x=1
vars=""
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
doaction=$(cat /var/www/html/pss/scripts/manualaction)

if [[ "$doaction" == "null" ]]
then
  exit 1
fi

actions=$(curl http://$database_ip/pss/other/dbupdate.php?type=manualaction\&device=$mac)

while IFS= read -r line; do
  number=${line:0:2}
  variables=${line:3}
  lgtype=${variables:0:1}
  echo "MESSAGE $datetime: Starting Manual Action ($number-$variables)" >> /home/pi/log/pss/$log.log

  case $number in
    11) bash /home/pi/scripts/pss/loopstart.sh $variables ;;
    12) bash /home/pi/scripts/pss/loopstop.sh ;;
    13) bash /home/pi/scripts/pss/tvpower.sh PS-1 ;;
    14) bash /home/pi/scripts/pss/tvpower.sh PE-1 ;;
    15) bash /home/pi/scripts/pss/cronsandmirror.sh manualmirror ;;
    16) bash /home/pi/scripts/pss/cronsandmirror.sh manualcrons ;;
    21) bash /home/pi/scripts/pss/tvinput.sh IS-1 ;;
    22) bash /home/pi/scripts/pss/tvinput.sh IS-2 ;;
    23) bash /home/pi/scripts/pss/tvinput.sh IS-3 ;;
    24) bash /home/pi/scripts/pss/tvinput.sh IS-4 ;;
    25) bash /home/pi/scripts/pss/tvinput.sh IS-5 ;;
    *) ;;
  esac

  sleep 1
done <<< "$actions"

sudo sh -c "echo 'null' > /var/www/html/pss/scripts/manualaction"
