#!/bin/sh

BASENAME=$(basename "$PWD")

rm -rf tmp
mkdir -p tmp/"$BASENAME"
if [ -d classes ]; then
  cp -R classes tmp/"$BASENAME"
fi
if [ -d config ]; then
  cp -R config tmp/"$BASENAME"
fi
if [ -d controllers ]; then
  cp -R controllers tmp/"$BASENAME"
fi
if [ -d docs ]; then
  cp -R docs tmp/"$BASENAME"
fi
if [ -d override ]; then
  cp -R override tmp/"$BASENAME"
fi
if [ -d sql ]; then
  cp -R sql tmp/"$BASENAME"
fi
if [ -d src ]; then
  cp -R src tmp/"$BASENAME"
fi
if [ -d translations ]; then
  cp -R translations tmp/"$BASENAME"
fi
if [ -d views ]; then
  cp -R views tmp/"$BASENAME"
fi
if [ -d upgrade ]; then
  cp -R upgrade tmp/"$BASENAME"
fi
if [ -d vendor ]; then
  cp -R vendor tmp/"$BASENAME"
fi
if [ -f logo.png ]; then
  cp logo.png tmp/"$BASENAME"
fi

cp index.php tmp/"$BASENAME"
cp "$BASENAME".php tmp/"$BASENAME"
cp config.xml tmp/"$BASENAME"
cp .htaccess tmp/"$BASENAME"
cp LICENSE tmp/"$BASENAME"
cp README.md tmp/"$BASENAME"
cd tmp && find . -name ".DS_Store" -delete
zip -r "$BASENAME".zip . -x ".*" -x "__MACOSX"
