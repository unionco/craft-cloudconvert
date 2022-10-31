# Craft CloudConvert
Thumbnail integration via CloudConvert for Craft CMS 3 & 4

## Versions and Compatibility
* 1.x.x -> Craft CMS 3
* 2.x.x -> Craft CMS 4

## Installation
TBD

Copy config file into your project:
`cp vendor/unionco/craft-cloudconvert/config.php config/cloudconvert.php`
and customize as needed.

Each config option is documented in `vendor/unionco/craft-cloudconvert/src/models/Settings.php` and a brief overview is below (Configuration)

## Configuration
* `apiKey` - (`string`) The CloudConvert API key. You must create your own CloudConvert account and generate your own API key.
* `thumbnailFolderId` - (`int`) Volume Folder ID where generating thumbnails should be stored.
* `sandbox` - (`bool`) Should CloudConvert operate in Sandbox mode?
* `enabled` - (`bool`) Should this plugin be enabled in this environment?
* `thumbnailWidth` - (`int`) Defaults to 300
* `thumbnailHeight` - (`int`) Defaults to 300
* `thumbnailFit` - (`string`) e.g. 'crop'

# Usage
* This plugin provides an interface for generating a thumbnail from a Craft Asset element. How you would like to hook it up with your CMS and business logic is up to you.
* *This plugin will not automatically do anything unless explicitly configured to do so by you*



# API
`CloudConvert` Service
(Work in progress)