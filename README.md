# Pimcore YML fixtures

Based on [Alice](https://github.com/nelmio/alice)

### How to install

```sh
composer require --dev youwe/pimcore-fixtures
```
*This plugin is only for DEV, do NOT install on a production server*

### How to load fixtures
Make sure "PimcoreFixtures" is enabled in `/website/var/config/extensions.php`
Place your fixtures in `/website/var/plugins/PimcoreFixtures/fixtures` named "001_object_name.yml", "002_object_name.yml" etc.

Load them with:
```sh
php pimcore/cli/console fixture:load
```
or
Go to Extensions \ PimcoreFixtures \ plugin settings \ Load fixtures

### Fixtures generator (still beta)
Go to Extensions \ PimcoreFixtures \ plugin settings
1. Object path: the root where to start generating fixtures
2. Object name: Recommended would be the object class ex. product, will translate into at [PIMCORE_ROOT]/website/var/plugins/PimcoreFixtures/fixtures/000_product.yml
3. Max level deep: Will stop at the specified level (default 1) and if greater then 1 then level will be appended to filename
4. Click generate. The generated files should be at [PIMCORE_ROOT]/website/var/plugins/PimcoreFixtures/fixtures/*.yml

### Useful links 
* [Alice documentation](https://github.com/nelmio/alice)
* [Faker documentation](https://github.com/fzaninotto/Faker)
* [Yml documentation](http://symfony.com/doc/current/components/yaml/yaml_format.html)


Todo:
* security checks / user restrictions
* better error handling in ext-js interface
* live progress when loading fixtures
