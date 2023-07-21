rm -rf tmp
mkdir -p tmp/bocustomize
cp -R docs tmp/bocustomize
cp -R config tmp/bocustomize
cp -R override tmp/bocustomize
cp -R translations tmp/bocustomize
cp -R views tmp/bocustomize
cp -R upgrade tmp/bocustomize
cp -R vendor tmp/bocustomize
cp -R index.php tmp/bocustomize
cp -R logo.png tmp/bocustomize
cp -R bocustomize.php tmp/bocustomize
cp -R config.xml tmp/bocustomize
cp -R LICENSE tmp/bocustomize
cp -R README.md tmp/bocustomize
cp -R composer.json tmp/bocustomize
cd tmp && find . -name ".DS_Store" -delete
zip -r bocustomize.zip . -x ".*" -x "__MACOSX"
