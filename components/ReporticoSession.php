<?php

namespace reportico\reportico\components;

use Yii;
use Reportico\Engine\ReporticoApp;


/**
 * Class to store global var
 * 
 */
class ReporticoSession
{
    public function __construct()
    {}

    public function __clone()
    {}

    // Ensure that sessions from different browser windows on same devide
    // target separate SESSION_ID
    static function setUpReporticoSession()
    {
        return;
    }

    /**
     * Does global reportico session exist
     * 
     * @return bool
     */
    static function existsReporticoSession()
    {
        $session_namespace_key = self::reporticoNamespace();
        return $session_namespace_key;
    }
    
    /*
     * Cleanly shuts doen session
     */
    static function reopenReporticoSession()
    {
        Yii::$app->session->open();
    }

    /*
     * Cleanly shuts doen session
     */
    static function closeReporticoSession()
    {
        Yii::$app->session->close();
        //session_write_close();
        //var_dump($session);
        //die;
    }

    static function sessionItem($in_item, $in_default = false)
    {
        $ret = false;
        if (self::issetReporticoSessionParam($in_item)) {
            $ret = self::getReporticoSessionParam($in_item);
        }

        if (!$ret) {
            $ret = false;
        }

        if ($in_default && !$ret) {
            $ret = $in_default;
        }

        self::setReporticoSessionParam($in_item, $ret);

        return ($ret);
    }

    static function sessionRequestItem($in_item, $in_default = false, $in_default_condition = true)
    {
        $ret = false;
        if (self::issetReporticoSessionParam($in_item)) {
            $ret = self::getReporticoSessionParam($in_item);
        }

        if (array_key_exists($in_item, $_REQUEST)) {
            $ret = $_REQUEST[$in_item];
        }

        if (!$ret) {
            $ret = false;
        }

        if ($in_default && $in_default_condition && !$ret) {
            $ret = $in_default;
        }

        self::setReporticoSessionParam($in_item, $ret);

        return ($ret);
    }

    /**
     * Check if a particular reeportico session parameter is set
     * using current session namespace
     * 
     * @param string $param Session parameter name
     * @param string $session_name Session name
     * 
     * @return bool 
     */
    static function issetReporticoSessionParam($param, $session_name = false)
    {
        $session = Yii::$app->session;
        $session_namespace_key = self::reporticoNamespace();
        return $session->has("$session_namespace_key.$param");
    }

    /**
     * Sets a reportico session_param using current session namespace
     * 
     * @param string $param Session parameter name
     * @param mixed $value Session parameter value
     * @param string $namespace Namespace session
     * @param array|bool ???? 
     * 
     * @return void
     */
    static function setReporticoSessionParam($param, $value, $namespace = false, $array = false)
    {

        $session = Yii::$app->session;
        $session_namespace_key = self::reporticoNamespace();
        $session->set("$session_namespace_key.$param", $value);

        if ( $param == "peter" )
        $session->set("peter", "yes".(new \Datetime())->format("H:i:s"));
        //echo " and ".self::getReporticoSessionParam("peter");
    }

    /**
     * Return the value of a reportico session_param
     * using current session namespace
     * 
     * @param string $param Session parameter name
     * 
     * @return mixed
     */
    static function getReporticoSessionParam($param)
    {
        $session = Yii::$app->session;
        $session_namespace_key = self::reporticoNamespace();
        if ( $session->has("$session_namespace_key.$param" ) )
            return $session->get("$session_namespace_key.$param");
        else
            return false;
    }

    /**
     * Clears a reportico session_param using current session namespace
     * 
     * @param string $param Session parameter name
     * @return void
     */
    static function unsetReporticoSessionParam($param)
    {
        $session = Yii::$app->session;
        $session_namespace_key = self::reporticoNamespace();
        if ( $session->has("$session_namespace_key.$param" ) )
            $session->remove("$session_namespace_key.$param");
    }

    /*
     **
     ** Register a session variable which will remain persistent throughout session
     */
    static function registerSessionParam($param, $value)
    {
        if (!self::issetReporticoSessionParam($param)) {
            self::setReporticoSessionParam($param, $value);
        }

        return self::getReporticoSessionParam($param);
    }

    /*
     ** Returns the current session name.
     ** Session variables exist
     ** using current session namespace
     */
    static function reporticoSessionName()
    {
        //if ( ReporticoApp::get("session_namespace") )
        if (self::getReporticoSessionParam("framework_parent")) {
            return "NS_" . self::reporticoNamespace();
        } else {
            return self::reporticoNamespace();
        }

    }

    /*
     ** Returns the current namespace
     */
    static function reporticoNamespace()
    {
        
        $namespace = ReporticoApp::get("session_namespace_key");
        if ( !$namespace ) {
            // No current namespace look in url parameters
            $namespace = "reportico";
            if (array_key_exists("reportico_session_name", $_REQUEST)) 
                $namespace = $_REQUEST["reportico_session_name"];
            if ( preg_match("/^NS_/", $namespace))
                $namespace = substr($namespace, 3);
            ReporticoApp::set("session_namespace_key", $namespace);
        }
        return $namespace;
    }

    /*
     ** initializes a reportico namespace
     **
     */
    static function initializeReporticoNamespace($namespace)
    {
        $session = Yii::$app->session;

        if ( $session->has($namespace) )
            $session->remove($namespace);
        $session->set($namespace, array());
        $session->set("$namespace.awaiting_initial_defaults", true);
        $session->set("$namespace.firsttimeIn", true);
    }
}
