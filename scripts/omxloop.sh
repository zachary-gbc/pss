#!/usr/bin/bash

#1 = Loop or Graphic
#2 = Loop File or Individual Video

if [[ $1 == "Loop" ]]
then
  readarray -t files < $2
  items=${#files[@]}
  item=0
  pssonoff=$(</home/pi/pssonoff)
  while [ true ]
  do
    omxplayer --no-keys ${files[$item]}
    item=$((item + 1))
    if [ $item -gt $items ]
    then
      item=0
      if [ ${pssonoff:0:3} == "off" ]
      then
        exit 1
      fi
    fi
  done
else
  omxplayer --no-keys --loop $2 &
fi
