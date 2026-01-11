# pss

## Steps for initialization of pi:
1. Follow steps to install new operating system and login using ssh or keyboard

1. sudo raspi-config (if not setup when installing OS)
  - System Options
    - Update Hostname (recommended to know which device is which but not required)
    - Wireless LAN
    - Update password (recommended but not required)
  - Interface Options
    - Enable SSH (recommended for remote access but not required)
  - Reboot

1. sudo apt-get update

1. sudo apt-get install git -y

1. git clone --depth=1 https://github.com/zachary-gbc/pss /home/pi/pss

1. bash /home/pi/pss/install.sh
  - Follow prompts on screen