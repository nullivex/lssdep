#!/bin/bash

echo "Starting LSSDep"

ifconfig eth0
route -n

echo "Waiting for network to settle"
sleep 2

echo "Installing packages"
aptitude -y update
aptitude -y install php5-cli

echo "Downloading tools"
wget -O /tmp/lssdep-live-tools.tar.gz http://$lssdepserver/lssdep-live-tools.tar.gz
tar xvzf /tmp/lssdep-live-tools.tar.gz -C /
rm -f /tmp/lssdep-live-tools.tar.gz

echo "Tools installed, starting LSSDep"

lssdep --live

