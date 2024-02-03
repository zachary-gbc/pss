#!/bin/bash

lanip=$(hostname -I)
dblan=${lanip%.*}

echo "Is this the Main PSS Instance? (Yes or No)"
read maininstall
if [[ $maininstall == "yes" ]]
then
  echo "Please Input a Database Name No Spaces Allowed (example churchname_prod)"
  read dbname
  echo "Please Input a User for the Database (No Spaces)"
  read dbuser
  echo "Please Input a Password for $dbuser (No Spaces)"
  echo "Keep it simple, this isn't Fort Knox here (example 'MyPassw0rd123!')"
  read dbpass
  echo ""
else
  echo "Input Main Host IP Address To Copy Settings:"
  read dbip
fi

install_log="/home/pi/install.log"
echo "Initiating Install" > $install_log
mkdir -p /home/pi/scripts
mkdir -p /home/pi/log

sudo apt-get update
sudo apt-get upgrade -y
appstoinstall=(awscli apache2 php php-mysql php-curl mariadb-server git wget curl cec-utils ffmpeg vlc vlc-bin)
for app in ${appstoinstall[@]}
do
  echo "--------------------" >> $install_log
  echo "Installing $app" >> $install_log
  sudo apt-get -qq install $app -y | tee -a $install_log
  echo "Completed Install of $app" >> $install_log
  echo "--------------------" >> $install_log
  echo "" >> $install_log
done

sudo mkdir -p /var/www/html/pss/scripts
sudo chown pi:pi /var/www/html
sudo chown pi:pi /var/www/html/pss/scripts
echo "never" > /home/pi/lastupdatecommit
cp /home/pi/pss/configs/pss.conf /home/pi/pss.conf
cp /home/pi/pss/scripts/ghupdate.sh /home/pi/scripts/ghupdate.sh
cp /home/pi/pss/scripts/pushover.sh /home/pi/scripts/pushover.sh
sudo cp -f /home/pi/pss/crons/general /etc/cron.d/general
sudo chown root:root /etc/cron.d/general
sudo rm /var/www/html/index.html
sudo rm /etc/cron.d/screens
sudo rsync -avu "/home/pi/pss/website/" "/var/www/html"

sudo mkdir -p /var/www/html/pss/files
sudo chown www-data:www-data /var/www/html/pss/files
sudo sed -i 's/upload_max_filesize.*/upload_max_filesize = 800M/' /etc/php/7.4/apache2/php.ini
sudo sed -i 's/post_max_size.*/post_max_size = 800M/' /etc/php/7.4/apache2/php.ini
sudo sed -i 's/bind-address.*/#bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf
sudo usermod -aG video www-data

if [[ $maininstall == "yes" ]]
then
  sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass'"
  sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'$dblan%' IDENTIFIED BY '$dbpass'"
  sudo mysql --user='root' -e "CREATE DATABASE $dbname"
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" < /home/pi/pss/db.txt
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" -e "INSERT INTO Variables(Var_Name, Var_Value) VALUES('Database-IP', '$dbip');"
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" -e "INSERT INTO Variables(Var_Name, Var_Value) VALUES('Database-Name', '$dbname');"

  sudo sed -i "s/database_name.*/database_name=$dbname/" /home/pi/pss.conf
  sudo sed -i "s/database_username.*/database_username=$dbuser/" /home/pi/pss.conf
  sudo sed -i "s/database_password.*/database_password=$dpass/" /home/pi/pss.conf
  sudo cp /home/pi/pss.conf /var/www/html/pss/conf/pss.conf
else
  sudo curl -Ss "http://$dbip/pss/conf/pss.conf" --output /home/pi/pss.conf
  sudo curl -Ss "http://$dbip/pss/conf/pss.conf" --output /var/www/html/pss/conf/pss.conf
  sudo sed -i "s/database_ip.*/database_ip=$dbip/" /home/pi/pss.conf
fi

sudo apt autoremove -y

echo ""
echo "----------------------"
echo "-- Install Complete --"
echo "----------------------"
echo "-- Plase Reboot Now --"
echo "----------------------"
