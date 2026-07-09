#!/bin/bash

. /var/www/conf/csdb.conf

lanip=$(hostname -I | tr -d ' ')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');

if [ "$main_or_remote" == "main" ]
then
  echo "MESSAGE $datetime: Starting loopcreate" >> /home/pi/log/pss/$log.log
  sudo curl -Ss http://$database_ip/pss/scripts/createloop.php >> /home/pi/log/pss/$log.log

  loopsquery=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "SELECT Lop_ID FROM Loops")
  loops=($loopsquery)

  for loop in ${loops[@]}
  do
    filename="/var/www/html/pss/files/loop-$loop.concat"
    if [[ -f "$filename" ]]
    then
      sudo ffmpeg -y -f concat -safe 0 -i $filename -c copy /var/www/html/pss/files/loop-$loop.mp4
      sleep 1
      sudo rm /var/www/html/pss/files/loop-$loop.concat
    fi
  done
  
  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "UPDATE Variables SET Var_Value='0' WHERE (Var_System='pss') AND (Var_Name='Background-Processing')")
fi
