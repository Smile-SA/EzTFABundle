# Installation

## Get the bundle using composer

Add SmileEzTFABundle by running this command from the terminal at the root of
your eZPlatform project:

```bash
composer require smile/ez-tfa-bundle
```

## Enable the bundle

To start using the bundle, register the bundle in your application's kernel class:

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Smile\EzTFABundle\SmileEzTFABundle(),
        // ...
    );
}

## Add Doctrine ORM support

edit your ezplatform.yml

```yaml
doctrine:
    orm:
        auto_mapping: true
```

## Update database schema

```console
php app/console doctrine:schema:update --force
```

## Configure bundle

Two providers are natively available:
* email
* sms
* u2f (no configuration needed)

### SMS Provider initialization

Subscribe to OVH SMS Service to obtain api keys

https://www.ovhtelecom.fr/sms/#order-SMS

Go to API key page to generate application and consumer keys

https://api.ovh.com/createToken/

### Providers configuration

```yaml
# app/config/config.yml
smileez_tfa:
    system:
        acme_site: # TFA is activated only for this siteaccess
            providers:
                email:
                    from: no-spam@your.mail # email provider sender mail
                sms:
                    application_key: <ovh_application_key>
                    application_secret: <ovh_application_secret>
                    consumer_key: <ovh_consumer_key>                    
```

Notes:
* don't activate TFA for all site, specially for back-office siteaccess
* you should use HTTPS for U2F authentication 

## Routing

```yaml
# app/config/routing.yml
tfa_auth:
    resource: "@SmileEzTFABundle/Resources/config/routing.yml"
    prefix:   /_tfa
```

## Assets

Add js assets to your layout

```twig
{% javascripts
    ...
    'bundles/smileeztfa/js/u2f-api.js'
    'bundles/smileeztfa/js/auth.js'
    ...
%}
    <script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```
