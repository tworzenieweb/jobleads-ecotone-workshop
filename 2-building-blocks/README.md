# Building Blocks Workshop

In this part of the workshop we will get familiar with higher level API - Building Blocks in [Ecotone Framework](https://docs.ecotone.tech/).

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
 

# Workshop task

In this exercise we will start using Aggregate pattern to model our domain.  
We will connect our `Order` Aggregate to Messaging and provide API to interact with it.

In this scenario, we will place and order and cancel it, which will trigger notification.  
If we run `run_example.php` it will end with success, yet current implementation contains of a lot of non-business code.
So we will be refactoring the code base to ensure only business code is left. 
We need to ensure that API will not change. Exposed functionality will be kept the same, however how we developed it will change.

## Executing the Workshop

Run command from console: `docker exec -it ecotone_demo php run_example.php`.  
After each step in exercise run this command to see if your solution is still valid.  

# Exercise Aggregate

1. Create `EcotoneOrderRepository` (implementing StandardRepository) to tell Ecotone how to fetch Order `Aggregate` (You can do it in `src/Infrastructure`). Take a look on `DoctrineORMOrderRepository` for reference :)
2.
a) Remove `placeOrder` method in `OrderService` and make `Order` class an `Aggregate`.  Set up factory method inside `Order` for `PlaceOrder` command.
b) Remove `DoctrineORMOrderRepository` and configure methods in `OrderRepository` interface as Repositories (By marking them with attributes).
3. Remove `OrderService` class and  Set up `CancelOrder` Command Handler in `Order` Aggregate and pass `Event Bus` to publish `OrderWasCancelled` event.
4. Remove `EventBus` reference from `CancelOrder` Command Handler and make use of Aggregate's recorded events to publish it.
5. Remove `CancelOrder` command class and use Command Handler Routing `order.cancel` in `Order` Aggregate 
6. Remove `EcotoneOrderRepository` and use Ecotone's inbuilt `Doctrine ORM Repository` to store `Order` in database (You can do in in `src/Infrastructure/EcotoneConfiguration.php)`.

### Hints

1. [How to implement Repository](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/repository#how-to-implement-repository)
2 
a) [Aggregate Factory Method](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#aggregate-factory-method)
b) [Configuring Repository as Interface](https://docs.ecotone.tech/modelling/command-handling/business-interface#business-repository-interface)
3. [Aggregate Action Method](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#aggregate-action-method)
4. [Recording events in Aggregate](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-event-handlers#publishing-events-from-aggregate)
5. [Aggregate Command Handler Routing](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#calling-aggregate-without-command-class)
6. [Inbuilt Doctrine ORM Repository](https://docs.ecotone.tech/modelling/command-handling/repository#inbuilt-repositories)