#!/bin/bash

. /var/www/html/pss/conf/pss.conf
lanip=$(hostname -I)
log=$(date -I)
datetime=$(date '+%Y-%m-%d %H:%M:%S');
echo "MESSAGE $datetime: Starting dbbackup" >> /home/pi/log/$log.log

mkdir -p /var/www/html/dbbackup

if [ "$database_ip" == "$lanip" ]
then
  sudo mysqldump --user="$database_username" --password="$database_password" $database_name -r /var/www/html/dbbackup/$database_name-$log.sql
  exit 1
else
  sudo wget -np -nH --cut-dirs 2 -mirror -R '*index*' -P /var/www/html/dbbackup http://$database_ip/dbbackup/
fi
