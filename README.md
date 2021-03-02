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
* Aims to have precise skipping of segments.
* Aims to have low CPU usage.

## Installation

You need [go-chromecast](https://github.com/vishen/go-chromecast) installed on your system, as well 
as PHP 7.2 or higher.

Download the `castblock-php.phar` file from the Releases page.

## Usage

Run `php castblock-php.phar` on a device that is in the same network as your Chromecast device or 
devices.

For example, you can install it as a service with systemd or run it in a screen.

## Contributions

Contributions via Issues or Pull Requests are welcomed.	 