#!/bin/bash

. /var/www/html/pss/conf/pss.conf

lanip=$(hostname -I | tr -d ' ')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');

if [ "$database_ip" == "$lanip" ]
then
  echo "MESSAGE $datetime: Starting loopcreate" >> /home/pi/log/$log.log
  sudo rm /var/www/html/pss/files/*.m3u
  sudo rm /var/www/html/pss/files/*.concat
  sudo curl -Ss http://$database_ip/pss/scripts/createloop.php >> /home/pi/log/$log.log

  loopsquery=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "SELECT Lop_ID FROM Loops")
  loops=($loopsquery)

  for loop in ${loops[@]}
  do
    filename=/var/www/html/pss/files/loop-$loop.concat
    if [[ -f "$filename" ]]
    then
      sudo ffmpeg -y -f concat -safe 0 -i $filename -c copy /var/www/html/pss/files/loop-$loop.mp4
    fi
    sleep 2
  done
fi
