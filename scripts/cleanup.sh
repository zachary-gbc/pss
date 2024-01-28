#!/bin/bash

# Delete old database backups
find /var/www/html/dbbackup -mtime +30 -type f -delete

# Delete old log files
find /home/pi/log -mtime +30 -type f -delete