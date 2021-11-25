# messaging

**becklyn/messaging** provides technology independent abstractions for asynchronous messaging. 

🚧 *Currently only a Redis backend is available!*

## Installation

```shell
composer require becklyn/messaging
```

## Getting started

Creating messages is done using a **MessageBuilder**.
By default, there is a JSON Builder based on Symfony Serializer.
Messages can be published using the **Publisher**:

```php
$builder = new SymfonyJsonSerializedMessageBuilder($serializer);
$message = $builder->build("content", "destination");

$publisher = new Publisher($connection, $serializer);
$publisher->publish($message);
```

Consuming messages is as simply as publishing them. For this task the library offers a **Consumer**.
Together with a **Worker**, Messages can be processed on delivery:

```php
class SampleWorker implements Worker
{
    public function execute(Message $message) : void
    {
        // TODO: Process the received message ...
        \var_dump($message);
    }
}
```

```php
$queue = new QueueDefinition("destination");
$params = new ConsumerParams();

$consumer = new Consumer($connection, $serializer, $lifecycleManager);
$consumer->consume(new SampleWorker(), $queue, $params);
```