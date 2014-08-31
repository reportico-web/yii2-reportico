<?php
namespace reportico\reportico\controllers;

use Yii;
use yii\web\Controller;
use reportico\reportico\components\reportico_datasource;
use yii\web\NotFoundHttpException;

class ReporticoController extends Controller
{
    public $engine = false;
    public $partialRender = true;
    public $defaultAction = 'admin';

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionReportico()
    {
        $this->engine = $this->module->getReporticoEngine();
	    return $this->renderPartial('index', array('engine' => $this->engine));
    }

    public function actionAjax()
    {
        $this->enableCsrfValidation = false;
        $this->engine = $this->module->getReporticoEngine();
	    return $this->renderPartial('index', array('engine' => $this->engine));
    }

    public function actionGraph()
    {
	    include("dyngraph_pchart.php");
    }

    public function actionDbimage()
    {
        $this->engine = $this->module->getReporticoEngine();
        $this->engine->set_project_environment($this->engine->initial_project, $this->engine->projects_folder, $this->engine->admin_projects_folder);

        $datasource = new reportico_datasource($this->engine->external_connection);
        $datasource->connect();

        $imagesql = $_REQUEST["imagesql"];
        $rs = $datasource->ado_connection->Execute($imagesql) 
            or die("Query failed : " . $ado_connection->ErrorMsg());
        $line = $rs->FetchRow();

        //header('Content-Type: image/gif');
        foreach ( $line as $col )
        {
            $data = $col;
            break;
        }
        echo $data;
        return false;
    }
}
