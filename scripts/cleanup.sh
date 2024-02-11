#!/bin/bash

log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting cleanup" >> /home/pi/log/$log.log

# Delete old database backups
find /var/www/html/dbbackup -mtime +30 -type f -delete

# Delete old log files
find /home/pi/log -mtime +30 -type f -delete