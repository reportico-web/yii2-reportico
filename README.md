Reportico Report Generator for Yii2
===================================

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist reportico/yii2-reportico "dev-master"
```

or add

```
"reportico/yii2-reportico": "dev-master"
```

to the require section of your `composer.json` file.

Once the extension is installed, simply use it in your code by adding to the modules section of your config/web.php file  the following:

```  ...
    'reportico' => [
            'class' => 'reportico\reportico\Module' ,
            'controllerMap' => [
                            'reportico' => 'reportico\reportico\controllers\ReporticoController',
                            'mode' => 'reportico\reportico\controllers\ModeController',
                            'ajax' => 'reportico\reportico\controllers\AjaxController',
                        ]
            ],
    ...
```

Quickstart and Documentation
============================

For the Yii2 Reportico quickstart guide go to :-
http://www.reportico.org/yii2/web/index.php/site/index

For the main reportico site go to :-
http://www.reportico.org

