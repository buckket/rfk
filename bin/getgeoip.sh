#!/bin/bash
wget -N -q http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz -O /tmp/GeoLiteCity.dat.gz

zcat /tmp/GeoLiteCity.dat.gz > ~/var/GeoLiteCity.dat
