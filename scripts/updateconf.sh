#!/bin/bash

. /home/pi/pss.conf

lanip=$(hostname -I)
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

if [[ $database_ip != $lanip ]]
then
  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Database-IP')")
  sudo sed -i "s/database_ip.*/database_ip=\"$query\"/" /home/pi/scripts/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Database-Name')")
  sudo sed -i "s/database_name.*/database_name=\"$query\"/" /home/pi/scripts/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Use-Pushover')")
  sudo sed -i "s/pushover_configured.*/pushover_configured=\"$query\"/" /home/pi/scripts/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Pushover-Token')")
  sudo sed -i "s/pushover_token.*/pushover_token=\"$query\"/" /home/pi/scripts/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Pushover-User-Key')")
  sudo sed -i "s/pushover_user_key.*/pushover_user_key=\"$query\"/" /home/pi/scripts/pss.conf

  query=$(mysql --user="$database_username" --password="$database_password" --database="$database_name" -e "SELECT Var_Value FROM Variables WHERE (Var_Name='Alert-On-IP-Change')")
  sudo sed -i "s/alert_on_ip_change.*/alert_on_ip_change=\"$query\"/" /home/pi/scripts/pss.conf

  sudo cp /home/pi/scripts/pss.conf /var/www/html/pss/conf/pss.conf
fi

if [[ $database_ip != $lanip ]]
then
  sudo curl -Ss "http://$main_db_ip/pss/conf/pss.conf" --output /home/pi/scripts/pss.conf
  sudo curl -Ss "http://$main_db_ip/pss/scripts/dbupdate.php?type=updateconf&device=$mac" >> /home/pi/log/$log.log
  sudo cp /home/pi/scripts/pss.conf /var/www/html/pss/conf/pss.conf
fi