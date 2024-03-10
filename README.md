# Kultur-API for Wordpress

Flexible, simple, safe. With Kultur-API for Wordpress, your association can quickly and easily exchange data between the website and the database, accept registrations and simplify processes. This wordpress plugin is initiated and maintained by Kulturwunsch Wolfenbüttel e. V. and its members.

**It only takes a quick installation from wordpress marketplace.**

## Support the project

Do you like the plugin and would you like to support the creators in continually developing it? Please donate an amount of your choice to us.

## Table of Contents

- [Getting started](#getting-started)
	- [Notes](#notes-1)
	- [Requirements](#requirements)
	- [Features](#features)
	- [Installation](#installation)
		- [Setting up Apache](#setting-up-apache)
		- [Setting up Nginx](#setting-up-nginx)
		- [Setting up IIS](#setting-up-iis)
		- [Configuration](#configuration)
		- [Helper functions](#helper-functions)
- [Routes](#routes)
	- [Basic routing](#basic-routing)
		- [Class hinting](#class-hinting)
		- [Available methods](#available-methods)
		- [Multiple HTTP-verbs](#multiple-http-verbs)
	- [Route parameters](#route-parameters)
		- [Required parameters](#required-parameters)
		- [Optional parameters](#optional-parameters)
		- [Regular expression constraints](#regular-expression-constraints)
		- [Regular expression route-match](#regular-expression-route-match)
		- [Custom regex for matching parameters](#custom-regex-for-matching-parameters)
	- [Named routes](#named-routes)
		- [Generating URLs To Named Routes](#generating-urls-to-named-routes)
	- [Router groups](#router-groups)
		- [Middleware](#middleware)
		- [Namespaces](#namespaces)
		- [Subdomain-routing](#subdomain-routing)
		- [Route prefixes](#route-prefixes)
	- [Form Method Spoofing](#form-method-spoofing)
	- [Accessing The Current Route](#accessing-the-current-route)
	- [Other examples](#other-examples)
- [Validation](#validation)
- [CSRF-protection](#csrf-protection)
	- [Adding CSRF-verifier](#adding-csrf-verifier)
	- [Getting CSRF-token](#getting-csrf-token)
	- [Custom CSRF-verifier](#custom-csrf-verifier)
	- [Custom Token-provider](#custom-token-provider)
- [Middlewares](#middlewares)
	- [Example](#example-1)
- [ExceptionHandlers](#exceptionhandlers)
	- [Handling 404, 403 and other errors](#handling-404-403-and-other-errors)
	- [Using custom exception handlers](#using-custom-exception-handlers)
		- [Prevent merge of parent exception-handlers](#prevent-merge-of-parent-exception-handlers)


___

# Getting started

To start, simply install the stable plugin from wordpress market place.

## Notes

This plugin was developed with the aim of adapting Wordpress websites as flexibly as possible to the needs of a cultural association. The goal is to eliminate the need for specialized knowledge or the need to regularly update static pages to provide current information. The plugin currently includes basic functions, but can be expanded at any time.

If you have ideas or suggestions, please write them in the Issues section or create a pull request. Fundamental changes to the structure or functionality must be discussed in the community before they are adopted into the base branch.

**What we won't cover:**

- How to setup a solution that fits your need. This is a basic demo to help you get started.
- Understanding of MVC; including Controllers, Middlewares or ExceptionHandlers.
- How to integrate into third party frameworks.

**What we cover:**

- How to get up and running fast - from scratch.
- How to get ExceptionHandlers, Middlewares and Controllers working.
- How to setup your webservers.

## Requirements

- PHP 7.4 or later
- PHP JSON extension enabled.
- PHP CURL extension enabled
- WordPress 6.0 or later

## Features

- Integrate external API (WUNSCH.events or custom)
- Manage event categories
- Manage imparting areas
- Contact Form 7 integration
	- Deactivate email sending
	- Transfer forms via API (New cultural guests and new organization members)

## Installation

Go to your plugin page. Then enter “Kultur-API for WP” in the search and click on install. Then you have installed the latest version of the plugin.

> [!TIP]
> We always recommend using the latest version from the WordPress marketplace, as this version is stable and has been checked for malfunctions.

___

# Contribute

The development of this plugin is supervised and coordinated by Kulturwunsch Wolfenbüttel e.V. However, it is desirable that everyone can contribute to this plugin and contribute their ideas.

## New functions

Completely new features that expand the scope of the plugin should be discussed before development. To do this, create a new issue and describe your idea. You should also mention whether it is a feature request or an idea that you can implement yourself. After a vote, the new idea can be submitted for review via a pull request. Please observe all standards.

## Further development and maintenance

### Structure of the plugin

The plugin uses a simple file structure that makes it possible to expand the range of functions between `Admin` and `Public` at any time.

```
- admin
	- css
	- js
	- partials
	- class-ka4wp-admin.php
	- index.php
- includes
- languages
- public
- tests
- index.php
- kultur-api-for-wp.php
- uninstall.php
```

### Prefix of functions and keys

This plugin works with prefixes. All custom entries (posts, taxonomy, pages, etc.) as well as all functions within the plugin must contain the prefix `ka4wp_`. This is the only way to ensure that there are no compatibility problems with other plugins and to show at all times whether it is a function of this plugin or the function is being used from outside.

### Language in the plugin

This plugin is designed for multilingualism. All texts that this plugin displays must be defined using a text domain. After deployment, new language strings can then be translated via the WordPress developer platform. The text domain of this plugin is `kultur-api-for-wp`.

> [!IMPORTANT]
> All original texts must be in english and the text must be assigned to text domain `kultur-api-for-wp`. A translation into another language (e.g. German) is then carried out via the developer platform.

### Nutzung der Datenbank

Beschreibung.

### WordPress standards

WordPress already offers functions for many possible actions in the core package. These can be viewed and checked [here](https://developer.wordpress.org/reference/functions/). In order to keep this plugin compatible for the future, these functions should be used wherever possible. The advantage is that the functions are maintained and updated by the WordPress team. If standard functions do not offer the desired scope, you are allowed to create your own functions in the respective scope (`Admin` or `Public`).

### Use of individual post types

Custom posttypes are used in this plugin. A custom posttype is a post that is created and managed by the plugin. WordPress uses custom posttypes to differentiate between pages, standard blog entries and posts created by plugins. The following table provides an overview of the custom posttypes used by this plugin:

| Type               | Description                                                                                                                                               |
|--------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------|
| `ka4wp_api` 		 | This type stores all information about the created interfaces. In addition to the predefined WUNSCH.events api, individual interfaces can also be created.|


> [!NOTE]
> When using created posts (e.g. in drop-down menus), you should always check whether they have the status `publish`.

### Use of Taxonomy

This plugin uses the Taxonomy function to store and provide standardized data. A new taxonomy can be created for each type of data and expanded with metadata. The following table shows which types currently exist and which fields have been expanded.

| Slug               | Description                                             | Additional meta data                                  |
|--------------------|--------------------------------------- -----------------|-------------------------------------------------------|
| `eventcategories`  | Saves all event categories and a description of them    | api_managed, enabled, timestamp, shortcut, databse_id |
| `impartingareas`   | Saves all imparting areas and a description of it       | api_managed, enabled, timestamp, databse_id           |


### Contact form 7

This plugin uses functions from "Contact Form 7". The following paragraph describes the integration and configuration options.

#### Form field datasets

By setting up the API for `eventcategories` and `imparting areas`, data sets can be used in forms. A data set makes it possible to dynamically design selection fields, radio buttons and checkboxes. The following example shows how data sets are used:
```
[select <fieldname> use_label_element data:<dataset_name>]
```

The following data sets are currently available:

| Key                           | Description                                                   |
|-------------------------------|---------------------------------------------------------------|
| `kulturapi_eventcategories`   | Contains all items present in the taxonomy `eventcategories`. |
| `kulturapi_impartingareas`    | Contains all items present in the taxonomy `impartingareas`.  |

You can find more information about how form fields work [here](https://contactform7.com/checkboxes-radio-buttons-and-menus/). Tips for developing new datasets can be found [here](https://www.zacfukuda.com/blog/cf7-dynamic-options) and [here](https://www.zacfukuda.com/blog/cf7-acf-dynamic-options).

#### Disable email sending

In the settings of a form you can deactivate that an email is sent after it has been sent. This setting is related to selecting an API.

### Corn jobs

Cron jobs can be generated in this plugin. Cron jobs are executed cyclically, depending on the system settings, and carry out actions. All cron jobs must be implemented in such a way that they are terminated and deleted when the plugin is deactivated or uninstalled. In addition, a check must be carried out when reactivating the plugin in order to reschedule any existing cron jobs.

### API Endpoints


### Test und Releases


### Dokumentation


### Changelog


## Hilfe und Support

This section will go into details on how to debug the router and answer some of the commonly asked questions- and issues.

### Hinweise zur Fehlermeldung

**Before reporting your issue, make sure that the issue you are experiencing aren't already answered in the [Common errors](#common-errors) section or by searching the [closed issues](https://github.com/skipperbent/simple-php-router/issues?q=is%3Aissue+is%3Aclosed) page on GitHub.**

To avoid confusion and to help you resolve your issue as quickly as possible, you should provide a detailed explanation of the problem you are experiencing.

### Procedure for reporting a new issue

1. Go to [this page](https://github.com/skipperbent/simple-php-router/issues/new) to create a new issue.
2. Add a title that describes your problems in as few words as possible.
3. Copy and paste the template below in the description of your issue and replace each step with your own information. If the step is not relevant for your issue you can delete it.

Remember that a more detailed issue- description and debug-info might suck to write, but it will help others understand- and resolve your issue without asking for the information.

**Note:** please be as detailed as possible in the description when creating a new issue. This will help others to more easily understand- and solve your issue. Providing the necessary steps to reproduce the error within your description, adding useful debugging info etc. will help others quickly resolve the issue you are reporting.

### Contribution development guidelines

- Please try to follow the PSR-2 codestyle guidelines.

- Please create your pull requests to the development base that matches the version number you want to change.

- Create detailed descriptions for your commits, as these will be used in the changelog for new releases.

- When changing existing functionality, please ensure that the unit-tests working.

- When adding new stuff, please remember to add new unit-tests for the functionality.

---




## Credits

######################

### API ENDPOINTS

#### custom

#### wunshc.events

#### implement your own


### Default functions

#### Verfügbarkeitsprüfung

Es findet eine Prüfung statt welches APIs verfügbar sind


