<?php
namespace reportico\reportico\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    public $engine = false;
    public $partialRender = true;

	public function actionIndex()
	{
        $this->engine = $this->module->getReporticoEngine();

        //$this->partialRender = Yii::app()->request->getQuery("partialReportico", true);
		$this->engine->access_mode = "FULL";
		$this->engine->initial_execute_mode = "ADMIN";
		$this->engine->initial_project = "admin";
		$this->engine->initial_report = false;
		$this->engine->clear_reportico_session = true;

        //$this->engine->bootstrap_styles = "3";
        //$this->engine->bootstrap_preloaded = false;

		if ( $this->partialRender )
        {
		    return $this->renderPartial('index', array('engine' => $this->engine));
        }
		else
        {
		    return $this->render('index', array('engine' => $this->engine));
        }
;
	}

	public function actionLogin()
	{
		return $this->render('index');
	}
}
