TgaAudience
===========

TgaAudience is a bundle for Symfony 2 which let you to mesure your website audience very easily:

Installation
---------------------

TgaAudience is a classic bundle, made for Symfony 2.2. You can use Composer to install it:

```
"tga/audience": "master"
```

Once installed, the bundle requires to store datas in the database. So, using doctrine, run:

```
php app/console doctrine:schema:update --force
```

After that, the bundle will run by itself. It will save required datas at the good time and will manage with HTTP requests
to find the better way to store them.

Usage
-----

However, even if the bundle will store alone the datas, to access at the statistics interface, you need to include the routing
in `routing.yml`:

```
tga_audience:
    resource: "@TgaAudienceBundle/Controller/"
    type:     annotation
    prefix:   /audience
```

After that, access to /audience and enjoy !