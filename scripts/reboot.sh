#!/usr/bin/bash

sleep 60

plannedreboot=$(</home/pi/plannedreboot)
if [[ $plannedreboot != *"planned reboot"* ]]
then
    sleep 60
    echo "planned reboot" > /home/pi/plannedreboot
    sleep 1
    sudo reboot now
fi

sleep 60

echo standby 0 | cec-client -s -d 1 &

echo "" > /home/pi/plannedreboot
