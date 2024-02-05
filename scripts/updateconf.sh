#!/bin/bash

. /var/www/html/pss/conf/pss.conf

lanip=$(hostname -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

if [[ $database_ip == $lanip ]]
then
  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Use-Pushover')")
  sudo sed -i "s/pushover_configured.*/pushover_configured=\"$query\"/" /var/www/html/pss/conf/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Pushover-Token')")
  sudo sed -i "s/pushover_token.*/pushover_token=\"$query\"/" /var/www/html/pss/conf/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Pushover-User-Key')")
  sudo sed -i "s/pushover_user_key.*/pushover_user_key=\"$query\"/" /var/www/html/pss/conf/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Alert-On-IP-Change')")
  sudo sed -i "s/alert_on_ip_change.*/alert_on_ip_change=\"$query\"/" /var/www/html/pss/conf/pss.conf
fi

if [[ $database_ip != $lanip ]]
then
  sudo curl -Ss "http://$database_ip/pss/conf/pss.conf" --output /var/www/html/pss/conf/pss.conf
  sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=updateconf&device=$mac" >> /home/pi/log/$log.log
fi