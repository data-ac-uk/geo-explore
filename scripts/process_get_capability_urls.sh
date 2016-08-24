#!/bin/bash
filename='../data/getCapabilitiesURLs.txt'
filelines=`cat $filename`
count=1
for line in $filelines ; do
	echo $line
	String1={$line,,}
	if [[ $String1 =~ .*"wms".* ]] ; then
		type="wms"
	fi
	
	if [[ $String1 =~ .*"wfs".* ]] ; then
		type="wfs"
	fi
    wget "$line" --timeout=10 -t 1 -O "Capabilities/$count-$type.xml"
    count=$((count+1))
done


