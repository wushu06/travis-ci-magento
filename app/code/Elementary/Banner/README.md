# Magento 2 Banner

This module will provide an easy to use banner widget

# Version:
1.0.1

# Developers:
Michael Cole
Arnolds Kozlovskis

## Installation

To use this module in magento 2, first you will need to modify the projects `composer.json` file. In the repositories node, you will need to add this block so that the module can be retrieved
```
{
    "type": "vcs",
    "url": "git@bitbucket.org:elementarydigital/magento2-module-banner.git"
}
```
Once this has been done, you can then run the command `composer require elementarydigital/magento2-banner` to add the module to the project. When this has been done, go to the `bin` folder in the magento 2 project and then to the following

1. run the module enabling command `php magento module:enable Elementary_Banner`
2. run the upgrade command with `php magento setup:upgrade`
3. run the static content command with the stores language so if its uk english it would be `php magento setup:static-content:deploy en_GB`

## User Guide

This module will allow you to add banner widgets across the store. banners are split into two sections, the banner itself and the slides shown in the banner. before a banner can be used, slides must first be made

### Adding Slides

Slides are added in the admin via Content > Banner Elements > Slides. Slides consist of an image, a title, content, and an option button. slides can also be disabled/enabled and a time period can be set against a slide to show it (i.e. timed offers).

### Adding Banners

Banners are added in the admin via Content > Banner Elements > Banners. When a banner is made, slides can be associated to the banner and the positions of the slides can also be set.

### Showing The Banner

The banner can be shown in one of two ways. The first way is selecting the widget in the admin and displaying it on a cms page or cms block.

The second way is directly adding it to the layout via xml like the example below.
```xml
<block class="Elementary\Banner\Block\Widget" name="banner">
    <arguments>
        <argument name="banner_id" xsi:type="string">1</argument>
    </arguments>
</block>
```