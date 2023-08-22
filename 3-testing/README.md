# Testing Workshop

In this part of the workshop we will get familiar with testing support in [Ecotone Framework](https://docs.ecotone.tech/).

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

In this exercise we will be focusing on tests only (`tests` folder).
Tests correctly fail and we will need to adjust them, so they can pass. 
Each tests contains @link to the documentation, that will help you with implementation.

We can modify all the code in tests besides `assert statements` and outside of tests `src` catalog should stay untouched :)   

## Executing the Workshop

Run command from console: `docker exec -it ecotone_demo vendor/bin/phpunit --stop-on-failure`.  
Whenever test will be set up correctly it will pass and move to another one.  
When all tests will be green, it means we've finished the workshop with success. 

### Hints

1. [How to implement Repository](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/repository#how-to-implement-repository)
2 
a) [Aggregate Factory Method](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#aggregate-factory-method)
b) [Configuring Repository as Interface](https://docs.ecotone.tech/modelling/command-handling/business-interface#business-repository-interface)
3. [Aggregate Action Method](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#aggregate-action-method)
4. [Recording events in Aggregate](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-event-handlers#publishing-events-from-aggregate)
5. [Aggregate Command Handler Routing](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#calling-aggregate-without-command-class)
6. [Inbuilt Doctrine ORM Repository](https://docs.ecotone.tech/modelling/command-handling/repository#inbuilt-repositories)