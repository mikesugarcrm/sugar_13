# JavaScript Unit Tests

These are the Javascript Jasmine unit tests that run via Karma. These tests must follow best practices for test writing by not writing to the filesystem, not making network calls, not crossing testing boundaries, etc.

## Prerequisites
* Node, npm, and gulp-cli are installed
* Chrome or another browser of choice is installed
* Mango is checked out (with sidecar)

## Install npm packages
```
cd /path/to/Mango/sugarcrm
npm install

cd /path/to/Mango/sugarcrm/sidecar
npm install
```

## Run the tests for Sugar
```
cd /path/to/Mango/sugarcrm
gulp karma --browsers Chrome
```

## Run the tests for Sidecar
```
cd /path/to/Mango/sugarcrm/sidecar
gulp karma --browsers Chrome
```

## Command line options
Option | Description
 ------|------------
-d --dev --debug | Set Karma options for debugging
--coverage | Enable code coverage
--ci | Enable CI specific options
--path <path> | Set base output path
--manual | Start Karma and wait for browser to connect (manual tests)
--team <name> | Filter by specified team
--file <path> | Filter by file path or glob expression. Multiple paths can be provided comma separated.
--browsers <list> | Comma-separated list of browsers to run tests with

## Other Examples:

### Run the Mango tests for the SFA team using Firefox
```
cd /path/to/Mango/sugarcrm
gulp karma --browsers Firefox --team sfa
```

### Run the Mango tests in debug mode for both Chrome and Firefox
```
cd /path/to/Mango/sugarcrm
gulp karma --browsers Chrome,Firefox --dev
```
