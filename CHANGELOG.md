# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Releases

### [0.1.12] - 2023-07-28

* Update README.md

### [0.1.11] - 2023-07-28

* Add cardinal direction and longitude and latitude to screen
* Add colors to longitude / latitude window
* Update README.md

### [0.1.10] - 2023-07-25

* Update README.md

### [0.1.9] - 2023-07-24

* Extend file path with vendor/ixnode/php-coordinate if file not found

### [0.1.8] - 2023-07-24

* Add cli command
* Add direction and degree to target coordinate

### [0.1.7] - 2023-07-23

* Update README.md

### [0.1.6] - 2023-07-22

* Update README.md

### [0.1.5] - 2023-07-22

* Add haversine formula to calculate distance on earth
* Add getDistance method to Coordinate class

### [0.1.4] - 2023-07-22

* Composer update
* Upgrading friendsofphp/php-cs-fixer (v3.21.1 => v3.22.0)
* Upgrading phpstan/phpstan (1.10.25 => 1.10.26)
* Upgrading povils/phpmnd (v3.1.0 => v3.2.0)

### [0.1.3] - 2023-07-22

* Possibility to specify float numbers in the Coordinate class

### [0.1.2] - 2023-07-10

* Add DMS parser
* Add README.md updates

### [0.1.1] - 2023-07-03

* Add Google Maps URL parser

### [0.1.0] - 2023-07-03

* Initial release with first Coordinate parser and converter
* Add src
* Add tests
  * PHP Coding Standards Fixer
  * PHPMND - PHP Magic Number Detector
  * PHPStan - PHP Static Analysis Tool
  * PHPUnit - The PHP Testing Framework
  * Rector - Instant Upgrades and Automated Refactoring
* Add README.md
* Add LICENSE.md

## Add new version

```bash
# Checkout master branch
$ git checkout main && git pull

# Check current version
$ vendor/bin/version-manager --current

# Increase patch version
$ vendor/bin/version-manager --patch

# Change changelog
$ vi CHANGELOG.md

# Push new version
$ git add CHANGELOG.md VERSION && git commit -m "Add version $(cat VERSION)" && git push

# Tag and push new version
$ git tag -a "$(cat VERSION)" -m "Version $(cat VERSION)" && git push origin "$(cat VERSION)"
```
