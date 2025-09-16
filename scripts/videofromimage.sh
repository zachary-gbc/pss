#!/usr/bin/bash

. /var/www/html/pss/conf/pss.conf
pssonoff="off"
pssonoff=$(</home/pi/pssonoff)
lanip=$(hostname -I | tr -d ' ')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');

if [ "$database_ip" == "$lanip" ] && [ ${pssonoff:0:3} == "off" ]
then
  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "UPDATE Variables SET Var_Value='1' WHERE (Var_Name='Background-Processing')")

  echo "MESSAGE $datetime: Starting videofromimage" >> /home/pi/log/$log.log
  sudo rm -f /var/www/html/pss/files/temp.png
  pngimages=$(ls /var/www/html/pss/files/*.png 2>/dev/null)
  jpgimages=$(ls /var/www/html/pss/files/*.jpg 2>/dev/null)
  images="$pngimages $jpgimages"

  for image in $images
  do
    file=${image#"/var/www/html/pss/files/"}
    fileid=${file::-4}
    id=${fileid::-2}
    lorp=${fileid:-1}
    echo "MESSAGE $datetime: Converting $image to video" >> /home/pi/log/$log.log

    sudo rm -f /var/www/html/pss/files/temp.png
    sudo convert $image -resize 1920x1080 -background black -gravity center -extent 1920x1080 /var/www/html/pss/files/temp.png

    sudo rm -f /var/www/html/pss/files/temp.mp4
    sudo ffmpeg -loop 1 -i /var/www/html/pss/files/temp.png -t 1 -pix_fmt yuv420p -vf scale=1920:1080 /var/www/html/pss/files/temp.mp4

    if [[ -f "/var/www/html/pss/files/temp.mp4" ]]
    then
      sudo rm -f /var/www/html/pss/files/$fileid.mp4
      sudo ffmpeg -stream_loop 9 -i /var/www/html/pss/files/temp.mp4 -c copy /var/www/html/pss/files/$fileid.mp4
      sudo rm -f /var/www/html/pss/files/$file
    fi
    sudo rm -f /var/www/html/pss/files/temp.mp4
    sudo rm -f /var/www/html/pss/files/temp.png
    sleep 2
  done
  
  bash /home/pi/scripts/videoconvert.sh &
fi
