# Booosta FTP module - Tutorial

## Abstract

This tutorial covers the ftp module of the Booosta PHP framework. If you are new to this framework, we strongly
recommend, that you first read the [general tutorial of Booosta](https://github.com/buzanits/booosta-installer/blob/master/tutorial/tutorial.md).

## Purpose

The purpose of this module is to connect to FTP(S) servers and exchange data.

## Installation

This module can be loaded with

```
composer require booosta/ftp
```

This also loads addtional dependent modules.

## Requirements

PHP must be compiled to support SSL for using FTPS.

## Usage

Instantiate an ftp object
```
$ftp = $this->makeInstance('ftp', $host, $user, $password, $options);

# $host can be an IP address or a DNS name
# $user and $password should be self explaining
# $options is an optional array that can hold the keys 'port', 'implicit_tls' and 'plaintext'

# This connects to the ftp server on port 21 using SSL encryption per default
$ftp = $this->makeInstance('ftp', 'ftp.example.com', 'arthur', 'theansweris42');

# This does not use SSL connection and connects to port 2121
$ftp = $this->makeInstance('ftp', 'ftp.example.com', 'arthur', 'theansweris42', ['plaintext' => true, 'port' => 2121]);

# This uses implicit TLS on default port 990
$ftp = $this->makeInstance('ftp', 'ftp.example.com', 'arthur', 'theansweris42', ['implicit_tls' => true]);

# Use passive mode for data transfer
$ftp->set_passivemode(true);

# Download a file from the server. $local_name is an optional name for the local file
$ftp->download($remote_file, $local_name);
$ftp->downlaod('question.pdf');
$ftp->downlaod('answer.pdf', 'downloaded_answer.pdf');

# Upload a file to the server. $remote_name is an optional name for the remote file
$ftp->upload($local_file, $remote_name);
$ftp->upload('file033333.pdf');
$ftp->upload('file837335.pdf', 'my2cents.pdf');

# Get the timestamp of a file on the server
$ts = $ftp->get_timestamp('documents/current_data.csv');
```
