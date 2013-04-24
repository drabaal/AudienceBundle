
TgaAudienceBundle
=================

TgaAudienceBundle is a complete, extensible and flexible bundle for Symfony 2 to track your website audience and
visitors behaviors easily.

Its aim is to be as simple as possible to install and to use.

Installation
------------

TgaAudience is a classic bundle, made for Symfony 2.2. You can use Composer to install it:

``` json
{
    "require": {
        "tga/audience": "dev-master"
    }
}
```

As a classic bundle, load it in your kernel :

``` php
$bundles = array(
	// ...
	new Tga\AudienceBundle\TgaAudienceBundle(),
	// ...
);
```

The bundle requires to store datas in the database. So, using doctrine, run:

```
php app/console doctrine:schema:update --force
```

To create the two required tables.

After that, the bundle will run by itself. It will save required datas at the good time and will manage with HTTP requests
to find the better way to store them. You don't need to worry about it!


Usage
-----

You may now want to view the datas stored by the bundle. There are two ways to do that:

### Using the default interface

The default interface is available by default. You just need to load routing:

``` yaml
tga_audience:
    resource: "@TgaAudienceBundle/Controller/"
    type:     annotation
    prefix:   /audience
```

And install the assets:

```
php app/console assets:install
```

Now, you can access the interface on http://mydomain.com/audience

### Using a custom interface

The default interface is just a simple way to display your statistics, but you may want to restrict
access, or load some more datas. If you want to create a custom display of your stats, it's really
easy.

The bundle give you a service to access all the calculated stats that you could view in the default
interface: this service is `tga_audience.stats`. Using it, you get the processor (the object that
analyse datas to find stats) and then every stat you want:

``` php
$processor = $this->get('tga_audience.stats')->getProcessor();

$processor->getUniqueVisitors();
$processor->getUniqueVisitorsCount();
$processor->getPageCalls();
$processor->getPageCallsCount();
$processor->getAverageVisitedPages();
$processor->getAverageDuration();
$processor->getAverageTimeToLoad();
$processor->getPlatforms();
$processor->getBrowsers();
$processor->getMostUsedRoutes();
$processor->getBrowsersVersions();
$processor->getExternalSources();
$processor->getMostUsedExternalSources();
```

Configuration
-------------

The default configuration is:

``` yaml
tga_audience:
    session_duration: 300       # Duration meanwhile a visitor is unique
    disabled_routes: []         # List of disabled routes to not track with the bundle
    environnements: ['prod']    # List of environnements where the bundle will track requests
```
