# Salesforce REST API PHP Wrapper

## The Story

I had the need to integrate a PHP web app with Salesforce, and given the humongous popularity of the platform, it was surprising that there was no library which provides a seamless way into its REST API.

Google results returned [these](https://github.com/bjsmasth/php-salesforce-rest-api) [two](https://github.com/jahumes/salesforce-rest-api-php-wrapper) packages, but neither was being actively developed, nor were they up to date with the recent state of Salesforce's API, and both were using a non-recommended authentication flow.

## Install

Via Composer

``` bash
composer require xhezairi/sforce-rest-api-php
```

### Recommended Salesforce API Readings

- [Build a Connected App for API Integration](https://trailhead.salesforce.com/content/learn/projects/build-a-connected-app-for-api-integration/implement-the-oauth-20-web-server-authentication-flow)
