<?php namespace reportico\reportico;

use Yii;
use yii\base\BootstrapInterface;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;


class Module extends \yii\base\Module implements BootstrapInterface
{
    public $engine;
    private $_assetsUrl;

    public $config;

    public function bootstrap($app)
    {  
        $app->getUrlManager()->addRules([
            $this->id => $this->id . '/default/index',
            $this->id . '/<id:\w+>' => $this->id . '/default/view',
            $this->id . '/<controller:\w+>/<action:\w+>' => $this->id . '/<controller>/<action>',
        ], false);
        $app->getUrlManager()->addRules([
            $this->id => $this->id . '/reportico/index',
            $this->id . '/<id:\w+>' => $this->id . '/default/view',
            $this->id . '/<controller:\w+>/<action:\w+>' => $this->id . '/<controller>/<action>',
        ], false);

    }

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'reportico\reportico\controllers';

    /**
     * @var array the list of IPs that are allowed to access this module.
     * Each array element represents a single IP filter which can be either an IP address
     * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
     * by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    /**
     * @var array|Generator[] a list of generator configurations or instances. The array keys
     * are the generator IDs (e.g. "crud"), and the array elements are the corresponding generator
     * configurations or the instances.
     *
     * After the module is initialized, this property will become an array of generator instances
     * which are created based on the configurations previously taken by this property.
     *
     * Newly assigned generators will be merged with the [[coreGenerators()|core ones]], and the former
     * takes precedence in case when they have the same generator ID.
     */
    public $generators = [];
    /**
     * @var integer the permission to be set for newly generated code files.
     * This value will be used by PHP chmod function.
     * Defaults to 0666, meaning the file is read-writable by all users.
     */
    public $newFileMode = 0666;
    /**
     * @var integer the permission to be set for newly generated directories.
     * This value will be used by PHP chmod function.
     * Defaults to 0777, meaning the directory can be read, written and executed by all users.
     */
    public $newDirMode = 0777;



    public function preinit()
    {
    }

    /*
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            $this->id => $this->id . '/default/index',
            $this->id . '/<id:\w+>' => $this->id . '/default/view',
            $this->id . '/<controller:\w+>/<action:\w+>' => $this->id . '/<controller>/<action>',
        ], false);
    }
    */

    function getAssetsUrl()
    {
        $assetsFolder = \Yii::getAlias("@reportico/reportico/assets");
        \Yii::$app->assetManager->publish($assetsFolder);
        return \Yii::$app->assetManager->getPublishedUrl($assetsFolder);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

/*
        if (!$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        foreach (array_merge($this->coreGenerators(), $this->generators) as $id => $config) {
            $this->generators[$id] = Yii::createObject($config);
        }

        $this->resetGlobalSettings();
*/

        return true;
    }

    // Create an instance of a reportico generator for Yii
    public function getReporticoEngine()
    {
        //$component = \Yii::$app->get('reportico');
        $this->engine = new components\reportico();
        components\set_up_reportico_session();

        //var_dump(Yii::$app->getUrlManager()->getUrlFormat());
        //if ( Yii::$app->getUrlManager()->getUrlFormat() == "get" )


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

        //$this->engine->url_path_to_assets = $this->app["url"]->asset($this->configGet("path_to_assets"));
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
        if ( !$this->config )
            $this->defaultConfig();
        return $this->config[$param];
    }

    public function defaultConfig()
    {
        $this->config  = array(
            'routing' => array(
                'prefix' => 'reportico',
                // 'subdomain' => 'faq.site.com',
            ),

            'framework_type' => 'yii',

            // Path relative to public where reportico assets are
            'path_to_assets' => 'packages/reportico/reportico',

            // Path relative to laravel pase or fully where projects will be created
            //'path_to_projects' => __DIR__."/components/projects",
            'path_to_projects' => \Yii::getAlias("@runtime/reportico/projects"),

            // Path relative to laravel pase or fully where admin project will be stored
            'path_to_admin' => __DIR__."/components/projects",

            // Bootstrap Features
            // Set bootstrap_styles to false for reportico classic styles, or "3" for bootstrap 3 look and feel and 2 for bootstrap 2
            // If you are embedding reportico and you have already loaded bootstrap then set bootstrap_preloaded equals true so reportico
            // doestnt load it again.
            'bootstrap_styles' => "3",
            'bootstrap_preloaded' => false,

            // In bootstrap enable pages, the bootstrap modal is by default used for the quick edit buttons
            // but they can be ignored and reportico's own modal invoked by setting this to true
            'force_reportico_maintain_modals' => false,

            // Indicates whether report output should include a refresh button
            'show_refresh_button' => false,

            // Jquery already included?
            'jquery_preloaded' => false,

            'bootstrap_styles' => "3",
            'bootstrap_preloaded' => false,

            // Engine to use for charts .. 
            // HTML reports can use javascript charting, PDF reports must use PCHART
            'charting_engine' => "PCHART",
            'charting_engine_html' => "NVD3",

            // Whether to turn on dynamic grids to provide searchable/sortable reports
            'dynamic_grids' => false,
            'dynamic_grids_sortable' => true,
            'dynamic_grids_searchable' => true,
            'dynamic_grids_paging' => false,
            'dynamic_grids_page_size' => 10,

            // Show or hide various report elements ( Use show or hide )
            'show_hide_navigation_menu' => "show",
            'show_hide_dropdown_menu' => "show",
            'show_hide_report_output_title' => "show",
            'show_hide_prepare_section_boxes' => "show",
            'show_hide_prepare_pdf_button' => "show",
            'show_hide_prepare_html_button' => "show",
            'show_hide_prepare_print_html_button' => "show",
            'show_hide_prepare_csv_button' => "show",
            'show_hide_prepare_page_style' => "show",

            // Static Menu definition
            // ======================
            // identifies the items that will show in the middle of the project menu page.
            // If not set will use the project level menu definitions in project/projectname/menu.php
            // To have no static menu ( for example if you just want to use a drop down then set to empty array )
            // To define a static menu, follow the example here.
            // report can be a valid report file ( without the xml suffix ).
            // If title is left as AUTO then the title will be taken form the report definition
            // Use title of BLANKLINE to separate items and LINE to draw a horizontal line separator
            // Exmaple
            // 'static_menu' => array (
                //array ( "report" => "an_xml_reportfile1", "title" => "<AUTO>" ),
                //array ( "report" => "another_reportfile", "title" => "<AUTO>" ),
                //array ( "report" => "", "title" => "BLANKLINE" ),
                //array ( "report" => "anotherfreportfile", "title" => "Custom Title" ),
                //array ( "report" => "", "title" => "BLANKLINE" ),
                //array ( "report" => "andanother", "title" => "Another Custom Title" ),
            //),
            //
            // To auto generate a static menu from all the xml report files in the project use
            //'static_menu' => array ( array ( "report" => ".*\.xml", "title" => "<AUTO>" ) ),
            //
            // To hide the static report menu
            //'static_menu' => array (),
            'static_menu' => false,

            // Dropdown Menu definition
            // ========================
            // Menu items for the drop down menu
            // Enter definition for the the dropdown menu options across the top of the page
            // Each array element represents a dropdown menu across the page and sub array items for each drop down
            // You must specifiy a project folder for each project entry and the reportfile definitions must point to a valid xml report file
            // within the specified project
            // Example :-
            // 'dropdown_menu' => array(
            //                array ( 
            //                    "project" => "projectname",
            //                    "title" => "dropdown menu 1 title",
            //                    "items" => array (
            //                        array ( "reportfile" => "report" ),
            //                        array ( "reportfile" => "anotherreport" ),
            //                        )
            //                    ),
            //                array ( 
            //                    "project" => "projectname",
            //                    "title" => "dropdown menu 2 title",
            //                    "items" => array (
            //                        array ( "reportfile" => "report" ),
            //                        array ( "reportfile" => "anotherreport" ),
            //                        )
            //                    ),
            //            ),
            'dropdown_menu' => false,
        );
    }
}
