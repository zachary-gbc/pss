#!/bin/bash

. /var/www/conf/csdb.conf
lanip=$(hostname -I | tr -d ' ')
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting cleanup" >> /home/pi/log/pss/$log.log

# Delete old log files
find /home/pi/log -mtime +30 -type f -delete

if [ "$main_or_remote" == "main" ]
then
    # Delete old deleted graphics
    mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e  "DELETE FROM pss_Graphics WHERE Gr_Delete='Y' AND Gr_UpdateDateTime<DATE_SUB(NOW(),INTERVAL 1 YEAR)"

    # Delete old manual actions
    mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e  "DELETE FROM pss_ManualActions WHERE MA_Acknowledge<DATE_SUB(NOW(),INTERVAL 1 MONTH)"
fi
