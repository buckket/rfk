#!/bin/bash

while [ ! -f ~/var/killliq ]
do
    liquidsoap /srv/radio/etc/radio.liq 2>> /srv/radio/var/log/liquidsoap_error.log;
echo 'liquidsoap crashed ... restarting in 10s ...';
sleep 10;
echo 'restarting ...'
done
rm ~/var/killliq