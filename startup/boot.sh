#!/bin/bash

echo "Starting LSSDep"

ifconfig eth0
route -n

echo "Waiting for network to settle"
sleep 2

# these packages have been merged into the deploy filesystem
#echo "Installing LSSDep required packages"
#aptitude -y update
#aptitude -y install php5-cli php5-curl lshw hdparm ethtool gettext gcc make

# new packages (should be merged)
aptitude -y update
aptitude -y install parted nginx bzip2
mkdir -p /var/www
service nginx start

echo "Installing ms-sys to modify Microsoft MBR's"
mkdir -p /tmp/ms-sys
wget -O /tmp/ms-sys/ms-sys.tar.gz http://$lssdepserver/content/ms-sys.tar.gz
tar xvzf /tmp/ms-sys/ms-sys.tar.gz -C /tmp/ms-sys
cd /tmp/ms-sys/ms-sys-2.3.0
make
make install
cd

# these packages have been merged into the deploy filesystem
#echo "Installing filesystem support for the following filesystems"
#echo "    ntfs,fat,vfat,fat16,fat32,ext2,ext3,ext4,xfs,btrfs,jfs,reiserfs,reiser4,swap"
#aptitude -y install ntfs-3g ntfsprogs fatresize fusefat fatattr xfsprogs btrfs-tools jfsutils reiserfsprogs reiser4progs

echo "Downloading tools"
wget -O /tmp/lssdep-live-tools.tar.gz http://$lssdepserver/lssdep-live-tools.tar.gz
mkdir -p /opt/lssdep
tar xvzf /tmp/lssdep-live-tools.tar.gz -C /opt/lssdep
ln -sf /opt/lssdep/lssdep.php /usr/bin/lssdep
rm -f /tmp/lssdep-live-tools.tar.gz

echo "Tools installed, starting LSSDep"

# GAY H4X BECAUSE THE DEBIAN DEVS DONT KNOW WHAT THEYRE DOING
livenetdev=$(printenv live-netdev)
if [ -z "$livenetdev" ]; then
	livenetdev=$lssdepnetdev
fi

LSSDEPCMD="lssdep -vvvv --live --netdev $livenetdev --server $lssdepserver --token $lssdeptoken"
echo "If there are problems, use the following command to rerun lssdep"
echo $LSSDEPCMD > /usr/bin/lssdep_run
chmod +x /usr/bin/lssdep_run
echo "lssdep_run"

$LSSDEPCMD