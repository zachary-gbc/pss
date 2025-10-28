#! /bin/bash

. /var/www/html/pss/conf/pss.conf
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
lanip=$(hostname -I | tr -d ' ')
echo "MESSAGE $datetime: Starting videoconvert" >> /home/pi/log/$log.log

inputfile="in.mp4"
outputfile="/home/pi/converted.mp4"
codec="h264_v4l2m2m"
codecparams="nal-hrd=cbr"
width="1920"
height="1080"
bitrate="5000000"
doublebitrate="10000000"
mode="CBR"
fps="30"
lastquery=0
secondsforconversion=300

if [ "$database_ip" != "$lanip" ]
then
  exit
fi

while true
do
  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "SELECT Gr_ID FROM Graphics WHERE (Gr_Converted='N') LIMIT 1")

  if [[ $query == "" ]]
  then
    break
  fi

  if [[ "$lastquery" == "$query" ]]
  then
    bash /home/pi/scripts/pushover.sh "$HOSTNAME" "tugboat" "Issue Converting Video $query"
    break
  fi

  # Portrait
  if [[ -f "/var/www/html/pss/files/$query-P.mp4" ]]
  then
    if [[ -f "/home/pi/converted.mp4" ]]
    then
      rm "/home/pi/converted.mp4"
      sleep 1
    fi
    
    lengthsec=$(ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 /var/www/html/pss/files/$query-P.mp4)
    length=$(echo "$lengthsec" | sed 's/\..*//')
    if [[ $length -lt $secondsforconversion ]]
    then
      ffmpeg -i "/var/www/html/pss/files/$query-P.mp4" -vcodec $codec -x264-params $codecparams -an -vf scale=$width:$height -b:v $bitrate -minrate $bitrate -maxrate $bitrate -bufsize $doublebitrate -r $fps $outputfile
      sleep 5
      sudo mv -f "/home/pi/converted.mp4" "/var/www/html/pss/files/$query-P.mp4"
      sleep 1
      echo "MESSAGE $datetime: Converted $query-P" >> /home/pi/log/$log.log
    else
      echo "MESSAGE $datetime: Video Too Long for Conversion $query-P" >> /home/pi/log/$log.log
    fi
    mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "UPDATE Graphics SET Gr_DurationP='$length' WHERE (Gr_ID='$query')"
  fi

  # Landscape
  if [[ -f "/var/www/html/pss/files/$query-L.mp4" ]]
  then
    if [[ -f "/home/pi/converted.mp4" ]]
    then
      rm "/home/pi/converted.mp4"
      sleep 1
    fi

    lengthsec=$(ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 /var/www/html/pss/files/$query-L.mp4)
    length=$(echo "$lengthsec" | sed 's/\..*//')
    if [[ $length -lt $secondsforconversion ]]
    then
      ffmpeg -i "/var/www/html/pss/files/$query-L.mp4" -vcodec $codec -x264-params $codecparams -an -vf scale=$width:$height -b:v $bitrate -minrate $bitrate -maxrate $bitrate -bufsize $doublebitrate -r $fps $outputfile
      sleep 5
      sudo mv -f "/home/pi/converted.mp4" "/var/www/html/pss/files/$query-L.mp4"
      sleep 1
      echo "MESSAGE $datetime: Converted $query-L" >> /home/pi/log/$log.log
    else
      echo "MESSAGE $datetime: Video Too Long for Conversion $query-L" >> /home/pi/log/$log.log
    fi
    mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "UPDATE Graphics SET Gr_DurationL='$length' WHERE (Gr_ID='$query')"
fi

  # Update DB
  mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e "UPDATE Graphics SET Gr_Converted='Y', Gr_UpdateDateTime=now() WHERE (Gr_ID='$query')"
  lastquery=$query
done

if [[ -f "/home/pi/converted.mp4" ]]
then
  rm "/home/pi/converted.mp4"
fi

bash /home/pi/scripts/loopcreate.sh &
