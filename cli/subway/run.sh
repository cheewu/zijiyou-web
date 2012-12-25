#! /bin/sh
DIR=$1
if [ ! -d $DIR ]; then
	echo "$DIR not exists"; exit
fi
for i in $(ls $DIR)
do
  php parse.php $DIR/$i
  echo $DIR/$i
done
