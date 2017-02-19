<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

// Create a DI
$di = new FactoryDefault();

//Registering a router
$di->set('router', function ()
{
    $router = new Router();
    require 'routes.php';
    return $router;
});

// Setup the view component
$di->set(
    "view",
    function () use ($config) {
        $view = new View();
        $view->setViewsDir($config->application->viewsDir);
        return $view;
    }
);

$di->set('dispatcher', function(){
    // Create/Get an EventManager
    $eventsManager = new Phalcon\Events\Manager();

    // Attach a listener
    $eventsManager->attach("dispatch", function ($event, $dispatcher, $exception) {
        // The controller exists but the action not
        if ($event->getType() == 'beforeNotFoundAction') {
            $dispatcher->forward(array(
                'namespace' => 'App\Controllers',
                'controller' => 'error',
                'action' => 'page404'
            ));
            return false;
        }
        // Alternative way, controller or action doesn't exist
        if ($event->getType() == 'beforeException') {
            switch ($exception->getCode()) {
                case Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(array(
                        'namespace' => 'App\Controllers',
                        'controller' => 'error',
                        'action' => 'page404'
                    ));
                    return false;
            }
        }
    });

    $dispatcher = new Phalcon\Mvc\Dispatcher();

    // Bind the EventsManager to the dispatcher
    $dispatcher->setEventsManager($eventsManager);

    return $dispatcher;
});

// Register a "response" service in the container
$di->set('response', function () {
    $response = new Response();
    return $response;
});

// Register a "request" service in the container
$di->set('request', function () {
    $request = new Request();
    return $request;
});

//add config and message
$di->set('config', $config, true);