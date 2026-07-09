#!/bin/bash

lanip=$(hostname -I)
dblan=${lanip%.*}
mac=$(cat /sys/class/net/wlan0/address | sed 's/://g')

if [[ ! -f "/var/www/conf/csdb.conf" ]]
then
    git clone --depth=1 https://github.com/zachary-gbc/csdb /home/pi/csdb
    bash /home/pi/csdb/install.sh subinstall
    sleep 5
else
    sudo apt-get update
    sudo apt-get upgrade -y
fi
echo "Will this system use (o)OMXPlayer or (v)VLC? (o or v)"
read omxvlc

. /var/www/conf/csdb.conf

mkdir -p /home/pi/log/pss
install_log="/home/pi/log/pss/pss_install.log"
echo "Initiating Install" > $install_log

if [[ $omxvlc == "o" ]]
then
  appstoinstall=(at cec-utils ffmpeg omxplayer)
else
  appstoinstall=(at curl cec-utils ffmpeg vlc vlc-bin)
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

sudo mkdir -p /var/www/html/pss/scripts
sudo chown pi:pi /var/www/html/pss/scripts
echo "never" > /home/pi/pss_lastupdatecommit
echo "off" > /home/pi/pssonoff
cp /home/pi/pss/pss.conf /var/www/conf/pss.conf
cp /home/pi/pss/scripts/ghupdate.sh /home/pi/scripts/pss/ghupdate.sh
sudo cp -f /home/pi/pss/pss.cron /etc/cron.d/pss
sudo chown root:root /etc/cron.d/pss
sudo rsync -avu "/home/pi/pss/website/" "/var/www/html/pss"
sudo chown www-data:www-data /var/www/html/pss/scripts/manualaction
sudo mkdir -p /var/www/html/pss/files
sudo chown www-data:www-data /var/www/html/pss/files
sudo usermod -aG video www-data

if [ "$main_or_remote" == "main" ]
then
    sudo mysql --user="$database_username" --password="$database_password" --database="$database_name" < /home/pi/pss/db.txt
fi
sudo curl -Ss "http://$database_ip/pss/scripts/dbupdate.php?type=devicedetails&device=$mac&devname=$HOSTNAME" >> $install_log

sudo sed -i 's/exit.*//' /etc/rc.local
sudo bash -c 'echo "/sbin/iw wlan0 set power_save off" >> /etc/rc.local'
sudo bash -c 'echo "" >> /etc/rc.local'
sudo bash -c 'echo "sleep 10 && /usr/bin/clear > /dev/tty1 &" >> /etc/rc.local'
sudo bash -c 'echo "" >> /etc/rc.local'
sudo bash -c 'echo "exit 0" >> /etc/rc.local'

sudo apt autoremove -y

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
