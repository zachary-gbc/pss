#!/bin/bash

. /var/www/html/pss/conf/pss.conf
log=$(date -I)
lanip=$(hostname -I)

mkdir -p /var/www/html/dbbackup

if [ $database_ip == $lanip ]
then
  sudo mysqldump --user="$database_username" --password="$database_password" $database_name -r /var/www/html/dbbackup/$database_name-$log.sql
  exit 1
else
  sudo wget -np -nH --cut-dirs 2 -mirror -R '*index*' -P /var/www/html/dbbackup http://$database_ip/dbbackup/
fi
