#!/usr/bin/bash

. /home/pi/pss.conf

lanip=$(hostname -I)

if [[ $database_ip == $lanip ]]
then
  pngimages=$(ls /var/www/html/pss/files/*.png 2>/dev/null)
  jpgimages=$(ls /var/www/html/pss/files/*.jpg 2>/dev/null)
  images="$pngimages $jpgimages"

  for image in $images
  do
    file=${image#"/var/www/html/pss/files/"}
    fileid=${file::-4}
    id=${fileid::-2}
    lorp=${fileid:-1}

    if [[ -f "/var/www/html/pss/files/temp.mp4" ]]
    then
      sudo rm /var/www/html/pss/files/temp.mp4
    fi
    sudo ffmpeg -loop 1 -i $image -t 1 -pix_fmt yuv420p -vf scale=1920:1080 /var/www/html/pss/files/temp.mp4

    if [[ -f "/var/www/html/pss/files/temp.mp4" ]]
    then
      sudo rm /var/www/html/pss/files/$fileid.mp4
      sudo ffmpeg -stream_loop 10 -i /var/www/html/pss/files/temp.mp4 -c copy /var/www/html/pss/files/$fileid.mp4
    fi
    sudo rm /var/www/html/pss/files/temp.mp4
    sleep 2
  done
fi