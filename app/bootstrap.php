<?php
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
/**
 * Description of bootstrap
 *
 * @author Webvizitky, Softdream <info@webvizitky.cz>,<info@softdream.net>
 * @copyright (c) 2014 - 2015, Miranthis a.s.
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




/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();

$di->set('config', $config);    
$di->set('assets',new \Assets(),true);

$di->set('request',function(){
    return new Core\Http\Request();
});

$di->set('formsManager',function(){
    return new Core\Form\Manager();
},true);

$di->set('response',function(){
    return new Core\Http\Response();
});
/**
  The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);

$di->set('cache',\Core\Cache::factory('data', 'memcache', $config->memcache->toArray()), true);

$di->set('templateEngine', function($view, $di) use($config) {
    $engine = new VoltEngine($view, $di);
    $engine->setOptions(
        array(
            'compiledPath' => $config->application->templateEngineCachePath
        )
    );

    $compiler = $engine->getCompiler();
    //register getImage snippet to template
    $compiler->addFunction('getImage',function($resolvedArgs,$exprArgs) use($compiler){
        $path = isset($exprArgs[0]['expr']) ? $compiler->expression($exprArgs[0]['expr']) : null;
        $base64 = isset($exprArgs[1]['expr']) ? $compiler->expression($exprArgs[1]['expr']) : false;
        $w = isset($exprArgs[2]['expr']) ? $compiler->expression($exprArgs[2]['expr']) : null;
        $h = isset($exprArgs[3]['expr']) ? $compiler->expression($exprArgs[3]['expr']) : null;
        return "\Core\File\Adapter\Image::getImage(".$path.",".$base64.",".$w.",".$h.")";
    });
    
    return $engine;
}, true);


/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    $config = array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
	'charset' => $config->database->charset
    );
    return new DbAdapter($config);
},true);

$di->set('security', function(){
    return new \Phalcon\Security();
}, true);

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function () {
    return new MetaDataAdapter();
});

/**
 * Start the session the first time some component request the session service
 */
$di->set('session', function () use ($config) {
    $session = new \Phalcon\Session\Adapter\Memcache((array) $config->memcache );
    $session->start();

    return $session;
}, true);

$di->set('cookies', function () {
    $cookies = new Phalcon\Http\Response\Cookies();
    $cookies->useEncryption(false);
    return $cookies;
}, true);


$di->set('flash', function(){
    $flash = new \Core\Flash\Session(array(
        'error'	    => 'alert alert-danger alert-dismissible alert-fixed',
        'success'   => 'alert alert-success alert-dismissible alert-fixed',
        'notice'    => 'alert alert-info alert-dismissible alert-fixed',
	'warning'   => 'alert alert-warning alert-dismissible alert-fixed'
    ));
    
    return $flash;
},true);

$di->set('cacheManager', function()use($config){
    return new \Core\Cache\Manager($config);
},true);

$di->set("elements",function(){
    $elements = new \Elements();
    
    return $elements;
},true);


$di->set('lang', function(){
    $lang = new Lang(array(
        'cache'         => null,
        'defaultLang'   => 'cz'
    ));
    
    return $lang;
},true);


$application = new \Phalcon\Mvc\Application($di);
$helper = new \Helper\Application($application);
//register routers and modules
$helper->registerModules();
$eventManager = new \Phalcon\Events\Manager();

//plugin loader
$pluginLoader = new \Plugin\Loader($application, $di, $config);
$pluginLoader->setPluginDirectory('../app/library/Plugin/');
$eventManager->attach('application', $pluginLoader);

//autoroute plugin
$autoRoutePlugin = new \Plugin\AutoRoute($eventManager);
$eventManager->attach('application', $autoRoutePlugin);
$eventManager->attach("dispatch",$autoRoutePlugin);

//access controll plugion
$accesControlPlugin = new \Plugin\Authentication\AccessControl($application,true);
$accesControlPlugin->disableModuleAcl('web');

$eventManager->attach('dispatch', $accesControlPlugin);

$di->set('menu', function()use($config){
    $menu = new \Plugin\Authentication\Components\Menu($config->menu);
    return $menu;
});

$application->setEventsManager($eventManager);

echo $application->handle()->getContent();

} catch (\Exception $e) {
    throw $e;
}



