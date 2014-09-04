<?php
namespace reportico\reportico\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/*
** ModeController -
** Generates reportico in a number of different modes:0-
** - Admin
*/
class ModeController extends Controller
{
    public $engine = false;
    public $partialRender = true;
    public $defaultAction = 'admin';

    public function actionReportico()
    {
        $this->engine = $this->module->getReporticoEngine();
        return $this->renderPartial('index', array('engine' => $this->engine));
    }

    /**
     * Run Reportico admin page
     */
    public function actionAdmin() {
        $this->enableCsrfValidation = false;
        $this->engine = $this->module->getReporticoEngine();
        $this->engine->access_mode = "FULL";
        $this->engine->initial_execute_mode = "ADMIN";
        $this->engine->initial_project = "admin";
        $this->engine->initial_report = false;
        $this->engine->clear_reportico_session = true;
	if ( $this->partialRender )
            return $this->renderPartial('index', array('engine' => $this->engine));
        else
            return $this->render('index', array('engine' => $this->engine));
    }

    /**
     * Generate singe report output
     */
    public function actionExecute() {
        $this->engine = $this->module->getReporticoEngine();
        $this->engine->access_mode = "REPORTOUTPUT";  // Run single report, no "return button"
        //$this->engine->access_mode = "SINGLEREPORT";  // Run single report, no access to other reports
        //$this->engine->Access_mode = "ONEPROJECT"; // Run single report, but with ability to access other reports

        $this->engine->initial_execute_mode = "EXECUTE";
        $this->engine->initial_project = \Yii::$app->request->get("project");
        $this->engine->initial_report = \Yii::$app->request->get("report");
        if ( !preg_match ( "/.xml$/", $this->engine->initial_report ) )
             $this->engine->initial_report .= ".xml" ;

        $this->engine->clear_reportico_session = true;
	    if ( $this->partialRender )
            return $this->renderPartial('index', array('engine' => $this->engine));
        else
            return $this->render('index', array('engine' => $this->engine));
    }

    /**
     * Generate output for a single report
     */
    public function actionMenu() {
        $this->engine = $this->module->getReporticoEngine();
        $this->engine->access_mode = "ONEPROJECT";
        $this->engine->access_mode = "ALLPROJECTS";  // Run single project menu, with access to other reports in other projects
        //$this->engine->Access_mode = "ONEPROJECT"; // Run single report, but with ability to access other reports
        $this->engine->initial_execute_mode = "MENU";
        $this->engine->initial_project = \Yii::$app->request->get("project");

        $this->engine->clear_reportico_session = true;
	    if ( $this->partialRender )
            return $this->renderPartial('index', array('engine' => $this->engine));
        else
            return $this->render('index', array('engine' => $this->engine));
    }

    /**
     * Run report in criteria entry mode
     */
    public function actionPrepare()
    {
        $this->engine = $this->module->getReporticoEngine();
        $this->engine->access_mode = "SINGLEREPORT"; // Allows running of a single report only
        $this->engine->access_mode = "ONEPROJECT";  // Run single report, but allow access to reports in other projects

        //$this->engine->access_mode = "ONEPROJECT";
        $this->engine->initial_execute_mode = "PREPARE";
        $this->engine->initial_project = \Yii::$app->request->get("project");
        $this->engine->initial_report = \Yii::$app->request->get("report");
        if ( !preg_match ( "/.xml$/", $this->engine->initial_report ) )
            $this->engine->initial_report .= ".xml" ;

        $this->engine->clear_reportico_session = true;
	    if ( $this->partialRender )
            return $this->renderPartial('index', array('engine' => $this->engine));
        else
            return $this->render('index', array('engine' => $this->engine));
    }
}
