# ClassyLlama DefaultBillingAddress Extension

## Description

This extension modifies the payment step on the checkout to pre-select the default billing address if it is different
than the shipping address.

## Installation Instructions

### Option 1 - Install extension by copying files into project

```bash
mkdir -p app/code/ClassyLlama/DefaultBillingAddress
git archive --format=tar --remote=git@bitbucket.org:classyllama/classyllama_defaultbillingaddress.git master | tar xf - -C app/code/ClassyLlama/DefaultBillingAddress/
bin/magento module:enable --clear-static-content DefaultBillingAddress
bin/magento setup:upgrade
bin/magento cache:flush
```

### Option 2 - Install extension using Composer if you are doing active development on the extension

```bash
composer config repositories.classyllama/extension-defaultbillingaddress git git@bitbucket.org:classyllama/classyllama_defaultbillingaddress.git
composer require classyllama/extension-defaultbillingaddress:dev-develop
bin/magento module:enable --clear-static-content ClassyLlama_DefaultBillingAddress
bin/magento setup:upgrade
bin/magento cache:flush
```

## Uninstallation Instructions

These instructions work regardless of how you installed the extension:

```bash
bin/magento module:disable --clear-static-content ClassyLlama_DefaultBillingAddress
rm -rf app/code/ClassyLlama/DefaultBillingAddress
composer remove classyllama/extension-defaultbillingaddress
mr2 db:query 'DELETE FROM `setup_module` WHERE `module` = "ClassyLlama_DefaultBillingAddress"'
bin/magento cache:flush
```
