# Resilient Messaging Workshop

In this part of the workshop we will get familiar with mechanisms that provide resiliency and recoverability in [Ecotone Framework](https://docs.ecotone.tech/).

# Requirements

In order to run the workshop only Docker is needed [Docker](https://docs.docker.com/engine/install/) and [Docker-Compose](https://docs.docker.com/compose/install/).

In case of lack of PhpStorm, Visual Studio Code will be helpful can help with editing code: https://code.visualstudio.com/ and Visual Studio Code extension, that will help with code completion: https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode 

# Installation

0. Open command line and go to the folder where this README.md file is located. Remember that all `docker-compose` commands will work only when you execute them being in the folder where docker-compose.yml file exists.
1. Run command `docker-compose pull && docker-compose up` to start the application.
2. When the container with the application starts, it will install all dependencies for us. You can check it by `docker logs -f ecotone_demo`
3. Start your IDE and open the folder where this README file is located.
4. We are ready, go to "workshop task" section 
5. After finishing the workshop run to clean up: `docker-compose down`
 

# Exercise Resilient Handling

In this exercise we have a task to build a stable functionality of placing an order.
Placing an order consists of two steps:

- Saving the order `Order`
- Calling an external service `ShippingService` to ship the order to the customer

## Executing the Workshop

Run command from console: `docker exec -it ecotone_demo php run_resilient_handling.php`.
Task will be finished with success, when no error will occur during execution.

# Step to take

`ShippingService` is external Service, so we cannot rely on its availability.
Therefore, we want to separate the order saving from `ShippingService` call and process the shipping of the order using an asynchronous processing.

1. Change `OrderService` to publish `OrderWasPlaced` `Event` instead of calling `ShippingService`.
2. Add an `EventHandler` that will listen to `OrderWasPlaced` and call `ShippingService` (You can create it in `src/Application/OrderService.php` class).
3. Add an asynchronous channel named `orders` that will send messages to RabbitMQ: `\Ecotone\Amqp\AmqpBackedMessageChannelBuilder::create("orders")` (You can create it in `src/Infrastructure/MessageChannelConfiguration.php` class).
4. Use this channel to process `EventHandler` (`OrderWasPlaced`) asynchronously (leave Command Handler synchronous).

### Hints

1, 2. [Event Handling and Publishing](https://docs.ecotone.tech/modelling/command-handling/external-command-handlers/event-handling#handling-events)
3, 4. [Asynchronous Message Processing](https://docs.ecotone.tech/modelling/asynchronous-handling#running-asynchronously)

# Exercise Resilient Sending

Currently, we are publishing Messages directly to Message Broker. Ecotone tries to make it as resilient as it's possible:
1. Ensures all Messages can be serialized before starting publishing
2. Retries Message when Message Broker is not available
3. Allows to send published Message to Error Channel when retries are exhausted

In this scenario our Message Broker will be unavailable for few seconds. 
We want to make sure that our application will not fail and will be able to self-heal when Message Broker will be available again.
In order to do so, we will set up Error Channel that will be used to send Messages that cannot be published to Message Broker.

## Executing the Workshop

Run command from console: `docker exec -it ecotone_demo php run_resilient_sending.php`.
Task will be finished with success, when no error will occur during execution.

# Step to take

1. In order to send messages that failed during sending to database deal letter.   
Add global pollable channel configuration with error channel pointing to `dbal_dead_letter` (You can do it `src/Infrastructure/MessageChannelConfiguration.php` class).
2. We want ensure even more consistency in our application by enabling Outbox pattern. To do it switch `ExceptionalQueueChannel` to `DbalBackedMessageChannelBuilder::create('notifications')` (In `src/Infrastructure/MessageChannelConfiguration.php` class)

### Hints
- [Error Channel](https://docs.ecotone.tech/modelling/resiliency/resilient-sending#error-channel)
- [Outbox Pattern](https://docs.ecotone.tech/modelling/resiliency/resilient-sending#outbox-pattern)