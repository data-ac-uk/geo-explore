#!/bin/bash
dir=`dirname $0`
cd $dir/../data/
rm data.gov.uk-ckan-meta-data-latest.csv.zip
rm datasets.csv resources.csv
wget https://data.gov.uk/data/dumps/data.gov.uk-ckan-meta-data-latest.csv.zip
/usr/bin/unzip data.gov.uk-ckan-meta-data-latest.csv.zip
egrep -Eio '(http|https)://[^/"].*?request=GetCapabilities' resources.csv | grep -v "," | sort | uniq > getCapabilitiesURLs.txt
