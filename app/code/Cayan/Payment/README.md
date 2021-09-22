# Cayan Payment Integration

This extension adds a new payment method to Magento 2 for the Cayan payment gateway. It also provides functionality for redeeming gift cards through Cayan.

## Prerequisites

* Linux or Unix server
* Apache 2.2+ or Nginx 1.10+
* PHP 7.0 or 7.1
* Magento Open Source or Magento Commerce 2.1 or 2.2
* A Cayan merchant account (contact your account representative for any necessary account information)

## Installation

The extension can be installed using Composer (_recommended_) or directly from the Git repository (available on Github).

### Composer

    $ composer require cayan/module-payment
    
### From Git

Clone the extension files in your Magento installation's `app/code` directory:

    $ cd /path/to/magento/app/code
    $ mkdir Cayan
    $ cd Cayan
    $ git clone https://github.com/Cayan-LLC/Magento.git Payment

## Post-installation

Complete the installation by running the following Magento commands:

    $ cd /path/to/magento
    $ php bin/magento setup:upgrade
    $ php bin/magento setup:di:compile
    
If the site is running in production deployment mode (`php bin/magento deploy:mode:show`), you will need to run this command as well:

    $ php bin/magento setup:static-content:deploy

## Configuration

All necessary configuration to get started is done in the Magento Admin by going to "Stores > Settings > Configuration > Payment > Cayan". For more details, please see the User Guide.

## Usage

Once configured, no further interaction with the merchant is required.

## Troubleshooting

The extension creates the following log files:

* "/path/to/magento/var/log/cayan_error.log"
* "/path/to/magento/var/log/cayan_debug.log" (If "Debug" setting is enabled.)

## Removal

Removal of the extension's files depends on the method of installation.

### Installed With Composer

If the extension was installed using Composer, please run the following commands to uninstall it:

    $ cd /path/to/magento
    $ php bin/magento module:uninstall Cayan_Payment

### Installed From Git

If the extension was installed using the package from Github, please run the following commands to uninstall it:

    $ cd /path/to/magento
    $ rm -r app/code/Cayan

### Data

For permanent removal, we recommend dropping the following tables and attributes from the store's database. **Please make a backup of the database before performing this task.**

#### Database Tables

* `cayan_codes` (Gift Cards)
* `cayan_codes_history` (Gift Card Usage History)
* `cayan_codes_in_quote` (Links Between Gift Cards and Orders)

#### Attributes

* `quote`: `cayan_giftcard_amount`

## Support

If you encounter any issues with this extension, please [contact Customer Support](https://help.cayan.com/18003-managing-my-account/how-can-i-contact-customer-support?) with the following details:

* What version of Magento you are using
* Relevant information about your hosting environment (host, OS version, PHP version, etc.)
* Steps to reproduce the problem
* Log entries containing the error encountered (if applicable)

We will respond to your request within 24-48 business hours.

## History

Please see the file CHANGES.md for a full history of changes by version. 

## Versioning

[Semantic Versioning](http://semver.org/) is used to denote new releases. For a list of available versions, see the Git repository's tags (`git tag -l`).

## Contributing

1. Fork the Git repository on GitHub
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request against the "develop" branch

## License

This extension is released under version 3.0 of the Open Software License (OSL-3.0). See the included LICENSE.txt file, or https://opensource.org/licenses/OSL-3.0 for more details.