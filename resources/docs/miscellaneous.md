<!---
title: Miscellaneous
author: Robin Radic
-->

#### Generated reports and documentation
- [PHPDoc](http://radic.nl:8080/job/laradic-config/PHPDOC_Report) API Documentation
- [PHPDox](http://radic.nl:8080/job/laradic-config/PHPDOX_Documentation) API Documentation
- [Code Coverage](http://radic.nl:8080/job/laradic-config/cloverphp) 
- [Jenkins](http://radic.nl:8080/job/laradic-config) latest job

#### Contributing
```bash
# Fork the github repository
git clone https://github.com/<yousername>/config
cd config
./scripts/bootstrap.sh 
# this will:
# init submodules
# copy git hooks
# add phing 

# To build the package
php phing build

# To clean the package
php phing clean

# Make sure it passes all unit tests
git add -A
git commit -m "I changed ... bcuz ..."
git push -u origin <branch>
```
