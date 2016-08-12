### Pimcore YML fixtures
Make sure "PimcoreFixtures" is enabled in `/website/var/config/extensions.php`
Place your fixtures in `/website/var/plugins/PimcoreFixtures/fixtures`
Load them with
```
php pimcore/cli/console fixture:load
```


### Useful links 
* [Alice documentation](https://github.com/nelmio/alice)
* [Faker documentation](https://github.com/fzaninotto/Faker)
* [Yml documentation](http://symfony.com/doc/current/components/yaml/yaml_format.html)