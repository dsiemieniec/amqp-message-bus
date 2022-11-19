# Amqp message bus

## Installation

1. Add repository in composer.json
```json
"repositories": [
    ...
    { "type": "vcs", "url": "https://github.com/dsiemieniec/amqp-message-bus" }
]
```
2. Require package
```shell
composer require dsiemieniec/amqp-message-bus
```
3. Add .env values
```
###> dsiemieniec/amqp-message-bus ###
RABBIT_CONNECTION=rabbitmq
RABBIT_PORT=5672
RABBIT_USER=guest
RABBIT_PASSWORD=guest
###< dsiemieniec/amqp-message-bus ###
```
4. Add `config/packages/amqp_message_bus.yaml` file with basic config
```yaml
amqp_message_bus:
  connections:
    default:
      host: '%env(RABBIT_CONNECTION)%'
      port: '%env(RABBIT_PORT)%'
      user: '%env(RABBIT_USER)%'
      password: '%env(RABBIT_PASSWORD)%'
```
5. Enable bundle

`config/bundles.php`
```php
<?php

return [
    ...
    Siemieniec\AmqpMessageBus\AmqpMessageBus::class => ['all' => true],
];
```

6. Declare default queue with command
```shell
bin/console amqp-message-bus:setup-rabbit
```
