# sc-stat-merge-sort

## Usage

```
bin/console process [sort-file] [person-files-dir] [output-dir]
```

### Options

#### --sort-match-column=[column-name]

Match the value from this column in the sorting spreadsheet with the related value in the person spreadsheet (default: "MatchValue")

#### --sort-pid-column=[column-name]

Name of the column in the sorting spreadsheet containing PID (default: "PID")

#### --person-match-column=[column-name]

Match the value from this column in the person spreadsheet with the related value in the sorting spreadsheet (default: "MatchValue")

## Windows Install

* Download [PHP 7.1 V14 x64 Non Thread Safe (x64)](http://windows.php.net/qa/) and unpack to C:\php
* Download and install [Visual C++ Redistributable for Visual Studio 2017 (x64)](http://www.microsoft.com/en-us/download/details.aspx?id=48145)
* Download and run [Composer installer](https://getcomposer.org/Composer-Setup.exe)
* Copy process-sample.bat and modify it to meet your requirements
