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

##Quickstart and Tutorials

After setup you can follow http://yii2_url/index.php/r=reportico or (http://yii2_url/index.php/reportico if you are using pretty urls) to access reportico. Then you can set an admin password, configure the tutorials or create new report project.

Use the tutorials to get to grips with report design, but for embedding and creating links to reportico from your Yii2 app follow the instructions in the following links.

For the Yii2 Reportico quickstart guide go to :-
http://www.reportico.org/yii2/web/index.php/site/index

For the main reportico site go to :-
http://www.reportico.org





## Screenshots


![Criteria Page](/images/reportico_prepare.png?raw=true "Criteria Page")


![Edit Query Page](/images/reportico_sql.png?raw=true "Edit Query Page")


![Report Output Page](/images/reportico_output.png?raw=true "Report Output Page")

