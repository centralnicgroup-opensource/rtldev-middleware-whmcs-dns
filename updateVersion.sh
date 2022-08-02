#!/bin/bash

# THIS SCRIPT UPDATES THE HARDCODED VERSION
# IT WILL BE EXECUTED IN STEP "prepare" OF
# semantic-release. SEE package.json

# version format: X.Y.Z
newversion="$1"
date="$(date +'%Y-%m-%d')"

printf -v sed_script 's/version\" => \"[0-9]+\.[0-9]+\.[0-9]+\"/version" => "%s"/g' "${newversion}"
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -E -i '' -e "${sed_script}" modules/addons/cnicdns/cnicdns.php
else
  sed -E -i -e "${sed_script}" modules/addons/cnicdns/cnicdns.php
fi

printf -v sed_script 's/"CentralNic Reseller DNS Addon v[0-9]+\.[0-9]+\.[0-9]+"/"CentralNic Reseller DNS Addon v%s"/g' "${newversion}"
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -E -i '' -e "${sed_script}" modules/addons/cnicdns/whmcs.json
else
  sed -E -i -e "${sed_script}" modules/addons/cnicdns/whmcs.json
fi

printf -v sed_script 's/"version": "[0-9]+\.[0-9]+\.[0-9]+"/"version": "%s"/g' "${newversion}"
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -E -i '' -e "${sed_script}" release.json
else
  sed -E -i -e "${sed_script}" release.json
fi

printf -v sed_script 's/"date": "[0-9]{4}-[0-9]{2}-[0-9]{2}"/"date": "%s"/g' "${date}"
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -E -i '' -e "${sed_script}" release.json
else
  sed -E -i -e "${sed_script}" release.json
fi
