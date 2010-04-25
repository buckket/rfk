#!/bin/bash

while [ ! -f ~/var/killliq ]
do
    liquidsoap ~/etc/radio.liq 2>> ~/var/log/liquidsoap_error.log;
echo 'liquidsoap crashed ... restarting in 10s ...';
sleep 10;
echo 'restarting ...'
done
