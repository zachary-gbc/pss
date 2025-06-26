#!/bin/bash

lanip=$(hostname -I)
dblan=${lanip%.*}
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

echo "Will this system use (o)OMXPlayer or (v)VLC? (o or v)"
read omxvlc
echo "Is this the Main PSS Instance? (y or n)"
read maininstall
if [[ $maininstall == "Y" ]] || [[ $maininstall == "y" ]]
then
  echo "Please Input a Database Name No Spaces Allowed (example churchname_prod)"
  read dbname
  echo "Please Input a User for the Database (No Spaces)"
  read dbuser
  echo "Please Input a Password for $dbuser (No Spaces)"
  echo "Keep it simple, this isn't Fort Knox"
  read dbpass
  echo ""
  dbip=$lanip
else
  echo "Input Main Host IP Address To Copy Settings:"
  read dbip
fi

install_log="/home/pi/pss_install.log"
echo "Initiating Install" > $install_log
mkdir -p /home/pi/scripts
mkdir -p /home/pi/log

sudo apt-get update
sudo apt-get upgrade -y
if [[ $omxvlc == "o" ]]
then
  appstoinstall=(at apache2 php php-mysql php-curl mariadb-server git wget curl cec-utils ffmpeg omxplayer)
else
  appstoinstall=(at apache2 php php-mysql php-curl mariadb-server git wget curl cec-utils ffmpeg vlc vlc-bin)
fi

for app in ${appstoinstall[@]}
do
  echo "--------------------" >> $install_log
  echo "Installing $app" >> $install_log
  sudo apt-get -qq install $app -y
  echo "Completed Install of $app" >> $install_log
  echo "--------------------" >> $install_log
  echo "" >> $install_log
done

sudo mkdir -p /var/www/html/pss/conf
sudo mkdir -p /var/www/html/pss/scripts
sudo chown pi:pi /var/www/html
sudo chown pi:pi /var/www/html/pss/scripts
sudo chown pi:pi /var/www/html/pss/conf
echo "never" > /home/pi/pss_lastupdatecommit
echo "off" > /home/pi/pssonoff
cp /home/pi/pss/configs/pss.conf /var/www/html/pss/conf/pss.conf
cp /home/pi/pss/scripts/ghupdate.sh /home/pi/scripts/ghupdate.sh
cp /home/pi/pss/scripts/pushover.sh /home/pi/scripts/pushover.sh
sudo cp -f /home/pi/pss/crons/pss /etc/cron.d/pss
sudo chown root:root /etc/cron.d/pss
sudo rm /var/www/html/index.html
sudo rsync -avu "/home/pi/pss/website/" "/var/www/html"
sudo chown www-data:www-data /var/www/html/pss/scripts/manualaction

phpversion=$(php -i | grep "PHP Version")
phpversionnumber=${phpversion:15:3}

sudo mkdir -p /var/www/html/pss/files
sudo chown www-data:www-data /var/www/html/pss/files
sudo sed -i 's/upload_max_filesize.*/upload_max_filesize = 800M/' /etc/php/$phpversionnumber/apache2/php.ini
sudo sed -i 's/post_max_size.*/post_max_size = 800M/' /etc/php/$phpversionnumber/apache2/php.ini
sudo sed -i 's/bind-address.*/#bind-address = 127.0.0.1/' /etc/mysql/mariadb.conf.d/50-server.cnf
sudo usermod -aG video www-data

if [[ $maininstall == "Y" ]] || [[ $maininstall == "y" ]]
then
  sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'localhost' IDENTIFIED BY '$dbpass'"
  sudo mysql --user='root' -e "GRANT ALL PRIVILEGES ON *.* TO '$dbuser'@'$dblan%' IDENTIFIED BY '$dbpass'"
  sudo mysql --user='root' -e "CREATE DATABASE IF NOT EXISTS $dbname"
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" < /home/pi/pss/db.txt
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" -e "INSERT INTO Variables(Var_Name, Var_Value) VALUES('Database-IP', '$lanip');"
  sudo mysql --user="$dbuser" --password="$dbpass" --database="$dbname" -e "INSERT INTO Variables(Var_Name, Var_Value) VALUES('Database-Name', '$dbname');"

  sudo sed -i "s/database_ip.*/database_ip=\"$dbip\"/" /var/www/html/pss/conf/pss.conf
  sudo sed -i "s/database_name.*/database_name=\"$dbname\"/" /var/www/html/pss/conf/pss.conf
  sudo sed -i "s/database_username.*/database_username=\"$dbuser\"/" /var/www/html/pss/conf/pss.conf
  sudo sed -i "s/database_password.*/database_password=\"$dbpass\"/" /var/www/html/pss/conf/pss.conf
else
  sudo curl -Ss "http://$dbip/pss/conf/pss.conf" --output /var/www/html/pss/conf/pss.conf
  sudo curl -Ss "http://$dbip/pss/scripts/dbupdate.php?type=devicedetails&device=$mac&devname=$HOSTNAME" >> $install_log
  echo "sudo curl -Ss "http://$dbip/pss/conf/pss.conf" --output /var/www/html/pss/conf/pss.conf"
  echo "sudo curl -Ss 'http://$dbip/pss/scripts/dbupdate.php?type=devicedetails&device=$mac&devname=$HOSTNAME'"
fi

sudo apt autoremove -y

. /var/www/html/pss/conf/pss.conf

echo ""
echo "----------------------"
echo "-- Main Pi IP: $database_ip --"
echo "-- Check Conf if IP Incorrect --"
echo "----------------------"

echo ""
echo "----------------------"
echo "-- Install Complete --"
echo "----------------------"
echo "-- Plase Reboot Now --"
echo "----------------------"
