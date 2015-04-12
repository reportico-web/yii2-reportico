<?php namespace reportico\reportico;

use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;


class Module extends \yii\base\Module //implements BootstrapInterface
{
    public $engine;
    private $_assetsUrl;

    public $config;

    public $defaultController='default';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'reportico\reportico\controllers';

    /**
     * Publish reportico assets
     */
    function getAssetsUrl()
    {
        $assetsFolder = \Yii::getAlias("@reportico/reportico/assets");
        \Yii::$app->assetManager->publish($assetsFolder);
        return \Yii::$app->assetManager->getPublishedUrl($assetsFolder);
    }

    // Create an instance of a reportico generator for Yii
    public function getReporticoEngine()
    {
        $this->engine = new components\reportico();
        components\set_up_reportico_session();

        if(Yii::$app->getUrlManager()->enablePrettyUrl)
        {
            $this->engine->reportico_ajax_script_url = $_SERVER["SCRIPT_NAME"]."/reportico/reportico/ajax";
            $this->engine->forward_url_get_parameters = false;
            $this->engine->forward_url_get_parameters_graph = "r=reportico/reportico/graph";
            $this->engine->forward_url_get_parameters_dbimage = "r=reportico/reportico/dbimage";
            $this->engine->reportico_ajax_mode = 2;
        }
        else
        {
            $this->engine->reportico_ajax_script_url = $_SERVER["SCRIPT_NAME"];
            $this->engine->forward_url_get_parameters = "r=reportico/reportico/ajax";
            $this->engine->forward_url_get_parameters_graph = "r=reportico/reportico/graph";
            $this->engine->forward_url_get_parameters_dbimage = "r=reportico/reportico/dbimage";
            $this->engine->reportico_ajax_mode = 1;
        }
        $this->engine->embedded_report = true;
        $this->engine->allow_debug = true;

        $this->engine->forward_url_get_parameters_graph = "reportico/graph";
        $this->engine->forward_url_get_parameters_dbimage = "reportico/dbimage";

        $this->engine->framework_parent = $this->configGet("framework_type");

        if ( \Yii::$app->user->id )
            $this->engine->external_user = \Yii::$app->user->id;
        else
            $this->engine->external_user = "guest";

        $this->engine->url_path_to_assets = $this->getAssetsUrl();

        // Where to store reportco projects
        $this->engine->projects_folder = $this->configGet("path_to_projects");
        if ( !is_dir($this->engine->projects_folder) )
        {
            $status = @mkdir($this->engine->projects_folder, 0755, true);
            if ( !$status )
            {
            if ( !$status )
                echo "Error cant create project area ".$this->engine->projects_folder."<BR>";
                die;
            }
        }
        $this->engine->admin_projects_folder = $this->configGet("path_to_admin");

        // Indicates whether report output should include a refresh button
        $this->engine->show_refresh_button = $this->configGet("show_refresh_button");

        // Jquery already included?
        $this->engine->jquery_preloaded = $this->configGet("jquery_preloaded");

        // Bootstrap Features
        // Set bootstrap_styles to false for reportico classic styles, or "3" for bootstrap 3 look and feel and 2 for bootstrap 2
        // If you are embedding reportico and you have already loaded bootstrap then set bootstrap_preloaded equals true so reportico
        // doestnt load it again.
        $this->engine->bootstrap_styles = $this->configGet("bootstrap_styles");
        $this->engine->bootstrap_preloaded = $this->configGet("bootstrap_preloaded");

        // In bootstrap enable pages, the bootstrap modal is by default used for the quick edit buttons
        // but they can be ignored and reportico's own modal invoked by setting this to true
        $this->engine->force_reportico_mini_maintains = $this->configGet("force_reportico_maintain_modals");

        // Engine to use for charts .. 
        // HTML reports can use javascript charting, PDF reports must use PCHART
        $this->engine->charting_engine = $this->configGet("charting_engine");
        $this->engine->charting_engine_html = $this->configGet("charting_engine_html");

        // Engine to use for PDF generation
        $this->engine->pdf_engine = $this->configGet("pdf_engine");

        // Whether to turn on dynamic grids to provide searchable/sortable reports
        $this->engine->dynamic_grids = $this->configGet("dynamic_grids");
        $this->engine->dynamic_grids_sortable = $this->configGet("dynamic_grids_sortable");
        $this->engine->dynamic_grids_searchable = $this->configGet("dynamic_grids_searchable");
        $this->engine->dynamic_grids_paging = $this->configGet("dynamic_grids_paging");
        $this->engine->dynamic_grids_page_size = $this->configGet("dynamic_grids_page_size");

        // Show or hide various report elements
        $this->engine->output_template_parameters["show_hide_navigation_menu"] = $this->configGet("show_hide_navigation_menu");
        $this->engine->output_template_parameters["show_hide_dropdown_menu"] = $this->configGet("show_hide_dropdown_menu");
        $this->engine->output_template_parameters["show_hide_report_output_title"] = $this->configGet("show_hide_report_output_title");
        $this->engine->output_template_parameters["show_hide_prepare_section_boxes"] = $this->configGet("show_hide_prepare_section_boxes");
        $this->engine->output_template_parameters["show_hide_prepare_pdf_button"] = $this->configGet("show_hide_prepare_pdf_button");
        $this->engine->output_template_parameters["show_hide_prepare_html_button"] = $this->configGet("show_hide_prepare_html_button");
        $this->engine->output_template_parameters["show_hide_prepare_print_html_button"] = $this->configGet("show_hide_prepare_print_html_button");
        $this->engine->output_template_parameters["show_hide_prepare_csv_button"] = $this->configGet("show_hide_prepare_csv_button");
        $this->engine->output_template_parameters["show_hide_prepare_page_style"] = $this->configGet("show_hide_prepare_page_style");

        // Static Menu definition
        // ======================
        $this->engine->static_menu = $this->configGet("static_menu");

        // Dropdown Menu definition
        // ========================
        $this->engine->dropdown_menu = $this->configGet("dropdown_menu");

/*
        $defaultconnection = $this->configGet("database.default");
        $useConnection = false;
        if ( $defaultconnection )
            $useConnection = $this->configGet("database.connections.$defaultconnection");
        else
            $useConnection = array(
                    "driver" => "unknown",
                    "dbname" => "unknown",
                    "user" => "unknown",
                    "password" => "unknown",
                    );
        $this->engine->available_connections = $this->configGet("database.connections");
*/
        $this->engine->external_connection = \Yii::$app->db->getMasterPdo();

        // Set Yii Database Access Config from configuration
        if ( !defined("SW_FRAMEWORK_DB_DRIVER") )
        {
            // Extract Yii database elements from connection string 
            $driver = "mysql";
            $host = "127.0.0.1";
            $dbname = "unnknown";
            if ( \Yii::$app->db->dsn )
            {
                $dbelements  = explode(':', Yii::$app->db->dsn);
                if ( count($dbelements) > 1 )
                {
                    $driver = $dbelements[0];
                    $dbconbits = explode(";", $dbelements[1]);
                    if ( preg_match("/mysql/", $driver ) )
                        $driver = "pdo_mysql";

                    foreach ( $dbconbits as $value )
                    {
                        $after = substr(strstr($value, "="), 1);
                        $pos = strpos($value, "=");
                        if ( $pos )
                        {
                            $k = substr($value, 0, $pos);
                            $v = substr($value, $pos + 1);
                            if ( $k == "host" || $k == "hostname" ) 
                                $host = $v;
                            if ( $k == "dbname" || $k == "database" ) 
                                $dbname = $v;
                        }
                    }
                }
            }
            define('SW_FRAMEWORK_DB_DRIVER', $driver);
            define('SW_FRAMEWORK_DB_USER',\Yii::$app->db->username);
            define('SW_FRAMEWORK_DB_PASSWORD',\Yii::$app->db->password);

            define('SW_FRAMEWORK_DB_HOST',$host);
            define('SW_FRAMEWORK_DB_DATABASE',$dbname);
        }

        return $this->engine;
    }    

    // Generate output
    public function generate()
    {
        $this->engine->execute();
    }

    public function configGet($param)
    {
        // Load defautl config parameters
        if ( !$this->config )
            require_once(__DIR__."/config.php");

        return $this->config[$param];
    }

}
