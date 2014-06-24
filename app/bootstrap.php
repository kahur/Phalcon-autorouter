<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
/**
 * Description of bootstrap
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2013, Webvizitky
 */


try {


$config = new \Phalcon\Config\Adapter\Json("../app/config/config.json");

$loader = new \Phalcon\Loader();
/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    array(
        $config->application->libraryDir,
        $config->application->pluginsDir
    )
)->register();

//register services



/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

$di->set('config', $config);

$di->set('request',function(){
    return new \Softdream\Http\Request();
});
/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);

//register template engine
$di->set('templateEngine', function($view, $di) use($config) {
    $engine = new VoltEngine($view, $di);
    $engine->setOptions(
        array(
            'compiledPath' => $config->application->templateEngineCachePath
        )
    );
    return $engine;
}, true);

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    return new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname
    ));
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});


//our awesome feature auto-routing start here :)
$application = new \Phalcon\Mvc\Application($di);
//we use the helper to auto register modules from configuration
$helper = new \Softdream\Helper\Application($application);
//register routers and modules
$helper->registerModules();
//create events manager
$eventManager = new \Phalcon\Events\Manager();
//Create instance of our awesome feature :)
$autoRoutePlugin = new \Softdream\Plugin\AutoRoute();
//attach AutoRoute plugin into application events
$eventManager->attach('application', $autoRoutePlugin);

$application->setEventsManager($eventManager);
//run the app!
echo $application->handle()->getContent();
} catch (\Exception $e) {
    throw $e;
}



