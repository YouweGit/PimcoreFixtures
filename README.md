# Pimcore YML fixtures
Make sure "PimcoreFixtures" is enabled in `/website/var/config/extensions.php`

```
php pimcore/cli/console fixture:load
```

Place your fixtures in `/plugins/PimcoreFixtures/fixtures` and make sure they are 
loaded in `/plugins/PimcoreFixtures/lib/Fixtures/Console/Command/LoadFixturesCommand.php`


### Useful links 
* [Alice documentation](https://github.com/nelmio/alice)
* [Faker documentation](https://github.com/fzaninotto/Faker)
* [Yml documentation](http://symfony.com/doc/current/components/yaml/yaml_format.html)