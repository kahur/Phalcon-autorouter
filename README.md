Phalcon-autorouter
==================

Example phalcon application with AutoRoute plugin

Dependencies
-------------------
* Phalcon 1.3.1 +
* \Softdream all Softdream library classes
 

Installation
-------------------
* See config/config.json 


How it works ?
-------------------

AutoRoute plugin gets params from URL and match first three params with module,controller,action eg.:
* First AutRoute plugin try compare first url part with modules and if module exist part will be removed from request and set default module into route.
* When the first part of url is not match with module the plugin will try set default module which is set in configuration
* When module is set from configuration the plugin will try match first parameter with controller of the module
* When controller is founded the param will be removed from request and sets as default controller into router
* When controller is not founded from url the plugin will again set from default configuration, if the default value is not set in configuration plugin will forward request to ErrorController with error404Action of the same module.
* Same proces is with first three params. if first doesn't match with module, plugin will try match with controller when doesn't match with controller plugin will try match with action when no one is matched, the plugin will set defaults if the defaults is set.

Example
-------------------
**Url: /admin/param/value/**

When we have set defaultController and defaultAction in configuration the url will be matched right as: module: Admin, controller: default controller from configuration (index), action: default action from controller (index) but if controller or action doesn't exist it will be forwarded to ErrorController, other parts of url will be stored in Request class.

**Url: /admin/mycontroller/param/value/**

The url will be matched too, but will be forwarded to mycontroller
etc.

Accessing params
---------------------
From controller is possible to access params from request

```PHP
//url /admin/mycontroller/param/value/
$param = $this->request->getParam("param");
```
The request will return value.

Accessing params with url map
----------------------------
In any reqeust it can be sat some map for url params.

```PHP
//url: /admin/mycontroller/mycustomvalue/mynextvalue/
$this->request->setMap(new \Softdream\Http\Url\Map("/:mycustomname/:mynextname");
$myParam1 = $this->request->getParam("mycustomname");// returns mycustomvalue
$myParam2 = $this->request->getParam("mynextname");//returns mynextvalue
```

Parts admin and mycontroller has been removed in AutoRouter due to parts are match with admin and controller. If the parts doesn't have been matched the return values will be : for mycustomname will be admin and for mynextvalue will be mynextvalue;

ENJOY!



