#!/bin/bash

. /var/www/html/pss/conf/pss.conf
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting cleanup" >> /home/pi/log/$log.log

# Delete old database backups
find /var/www/html/dbbackup -mtime +30 -type f -delete

# Delete old log files
find /home/pi/log -mtime +30 -type f -delete

# Delete old deleted graphics
mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e  "DELETE FROM Graphics WHERE Gr_Delete='Y' AND Gr_UpdateDateTime<DATE_SUB(NOW(),INTERVAL 1 YEAR)"

# Delete old manual actions
mysql --user="$database_username" --password="$database_password" --database="$database_name" -N -e  "DELETE FROM ManualActions WHERE MA_Acknowledge<DATE_SUB(NOW(),INTERVAL 1 MONTH)"
