#!/usr/bin/bash

if [[ $1 == "0" ]]
then
  sudo reboot now
fi

sleep 60

echo standby 0 | cec-client -s -d 1 &