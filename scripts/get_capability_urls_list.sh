#!/bin/bash
dir=`dirname $0`
cd $dir/../data/
if [ "$1" == "download" ]; then
	rm data.gov.uk-ckan-meta-data-latest.csv.zip
	rm datasets.csv resources.csv
	wget https://data.gov.uk/data/dumps/data.gov.uk-ckan-meta-data-latest.csv.zip
	unzip data.gov.uk-ckan-meta-data-latest.csv.zip
	rm getCapabilitiesURLs.txt
fi
egrep -Ei "(http|https)://" resources.csv | egrep -Ei "request=GetCapabilities" | egrep -Eio "(http|https)://[^,]*" | sort -u > getCapabilitiesURLs.txt
