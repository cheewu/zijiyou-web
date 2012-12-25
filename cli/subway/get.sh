#! /bin/sh
AREA=$1
BASE_URL=$(cat url.txt)
if [ ! -d $AREA ]; then
	mkdir $AREA
fi
for i in $(cat "$AREA"".txt")
do
	NAME_ENCODING=$(php -r "echo rawurlencode('$i');")
	echo $i
	curl -s "$BASE_URL$NAME_ENCODING""RDT" | grep BHF > $AREA/$i.txt
done
