Phalcon-autorouter
==================

Version 2.0
-------------------

Example full application with various libraries included in this example

**Change log**
* Structure have been changed and updated to more clear and better working
* Added support to define full custom folder structure and set it in configuration file
* Add various usable libraries and features as plugin Installer, Observer, Directory, File adapter, Image, Mail etc.
* Observer support to define custom observer events on both sides, then execute events see example in plugin structure. Module observer listeners can be registered in module config file and events in installation script of plugin. See example config and plugin.
* Added support for rest routing
* Added support to define custom rest routing actions
* Added support for Phalcon 1.0 in AutoRoute plugin and AccessControll
* Fix matching url to route
* Improve loading and performance of access controll
* Version 1.0 it's not more supported, for older projects please use branch v1 to access old AutoRouting plugin
* Sofdream namespace and folder have been removed in new version is Core

Dependencies
-------------------
* Phalcon 1.3.1 +
* \Core, \Helper, \Plugin and all libraries included in library
* Multi-Module Phalcon app
 

Installation
-------------------
* Copy contents of projects
* Edit app/config/config.json and update db and path settings if neccessary


How it works ?
-------------------

AutoRoutin plugin route application based on URL in order /module/controller/action/. When module, controller or action
have not been found AutoRoute plugin automaticaly shift route for specific part and set it for next part eg.:
/module/controller/action/ where module is not been found then automatic default module have been used and module part is set as an "controller".
Same process happening until all parts are defined or found. Error action is being used when no default actions are set in configuration.

Example
-------------------
**Url: /manager/param/value/**

Url will be routed to: manger module with indexAction rest of parameters behind /manager/ will be parsed and accessible in order /key/value/ in this case $this->request->getParam('param') will return value.

**Url: /admin/my-own/param/value/**

Url will be routed to: manger module to myOwnController and indexAction rest of parameters behind /manager/my-own/ will be parsed and accessible in order /key/value/ in this case $this->request->getParam('param') will return value.


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

To access part's what have been shifted (/admin/mycontroller/) durring auto routing process we can use

```PHP
$this->request->getParam("module"); // to access module name ( admin )
$this->request->getParam("controller"); //to access url controller value
$this->request->getParam("action"); //to access url action name 
```

ENJOY!



