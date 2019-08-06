# DrupalHost

DrupalHost is a Drupal Installer plugin similar to the `laravel/installer` plugin. It only supports installation for Drupal `8.*` versions.

## Installation

1. First, download the Drupal installer using Composer:

    ```shell
        composer global require ankitjain28may/drupalhost
    ```

2. Once it is installed, the `drupalhost` new command will create a fresh Drupal installation in the directory you specify.

    ```shell
        drupalhost new blog
    ```

**Note:** It takes the latest release i.e `8.7.5` of the [Drupal Releases](https://www.drupal.org/project/drupal/releases) 

We can also install our specific version by running this command-

    ```shell
        drupalhost new blog 8.7.4       # install drupal 8.7.4 release
    ```

    ```shell
        drupalhost new blog composer    # install drupal-composer
    ```
**Note:** Passing `composer` as a version installs the [drupal-composer](https://github.com/drupal-composer/drupal-project) project.

## Contribute

>Feel free to contribute

## License

>Copyright (c) 2019 Ankit Jain - Released under the MIT License