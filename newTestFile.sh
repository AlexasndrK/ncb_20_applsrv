#! /bin/bash

#This is new script

# Network interface
PUBLIC_INF=eth0
PRIVAT_INF=eth1

# Location of iptables
IPT='/sbin/iptables'

# sysctl
SYSCTL="/sbin/sysctl -w"

IPADDR_PUB=`/sbin/ifconfig $PUBLIC_INF | awk '/inet addr/ { print $2 } ' | sed -e s/addr://`
IPADDR_PRV=`/sbin/ifconfig $PRIVAT_INF | awk '/inet addr/ { print $2 } ' | sed -e s/addr://`

function startfw {

  echo -n "Starting Firewall Services... "
  echo 
  
  # If IPADDR is null or empty, then dont do anything
  if [ -z "$IPADDR_PUB" ] 
  then
     echo "Unable to start firewall. No connection available. "
     exit
  fi
  }
