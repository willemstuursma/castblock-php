# CastBlock PHP

A utility to automatically skip sponsorship mentions in Youtube videos playing on your Chromecast.

This software is intended to run as a service in your home network, for example by running it on a
Raspberry Pi or similar.

Heavily inspired by [stephen304/castblock](https://github.com/stephen304/castblock).

The sponsorship segments are provided by the [Sponsorblock](https://sponsor.ajay.app/) browser extension.

## Features

* Automatically skip sponsorship segments in Youtube videos playing on Chromecast or Google Home mini devices.
* Detects new Chromecasts in your network, supports multiple Chromecasts.
* Protect your privacy and your unlisted videos by not sharing video IDs with the Sponsorblock API.
* Aims to have precise skipping of segments and prevent unnecessary skipping.
* Aims to have low CPU usage.

## Installation

You need [go-chromecast](https://github.com/vishen/go-chromecast) installed on your system, as well
as PHP 7.2 or higher.

Download the `castblock-php.phar` file from the Releases page.

## Usage

Run `php castblock-php.phar` on a device that is in the same network as your Chromecast device or
devices.

For example, you can install it as a service with systemd or run it within `screen`.

By setting up a systemd service, you can have systemd automatically start castblock-php on system startup, persist the logs, monitor the process, and restart castblock-php automatically if needed.
You can use [castblock.service](castblock.service) as an example systemd service. Documentation for how to use [the service file](castblock.service) is within [the file](castblock.service) itself.

### Options

* `--category`: Select which category to skip. Repeat multiple times to skip multiple categories. Default: `--category sponsor --category interaction`.

You can adjust the debugging verbosity by adding `-v`, `-vv` or `-vvv`.

## Contributions

Contributions via Issues or Pull Requests are welcomed.