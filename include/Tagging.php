<?php

namespace LTOC;

use OpenCalais\Exception\OpenCalaisException;
use OpenCalais\OpenCalais;

class Tagging
{
    private static $_instance = null;

    private $api_key;
    private $relevance;
    private $add_tag_on_save;
    private $batch_posts;
    private $included_categories;
    private $blacklist;
    private $disable_batch;

    private static $STATUS_EXCLUDED_CATEGORY = 1;
    private static $STATUS_TAGGED = 2;
    private static $STATUS_NO_TAGS_FOUND = 3;
    private static $STATUS_OC_ERROR = 4;

    private $plugin_option_defaults = array(
        'ltoc_api_key' => '',
        'ltoc_tag_relevance' => 20,
        'ltoc_add_tag_on_save' => 'on',
        'ltoc_tag_blacklist' => "",
        'ltoc_batch_posts' => 20,
        'ltoc_included_categories' => '',
        'ltoc_disable_batch' => 'off'
    );

    public function __construct()
    {

        // load plugin options
        $this->loadPluginOptions();

        // process requests with action parameter
        $this->actionRoutes();

        // load hooks but only for admin user
        if (is_admin()) {
            $this->addActions();
        }

        // run cron job
        add_action('ltoc_tagging_event', array($this, 'batchTagging'));

        // do sitemap setup
        add_action('template_redirect', array($this, 'loadSitemap'));
    }

    private function __clone()
    {
    }

    // Have a single globally accessible static method
    public static function getInstance()
    {
        if (!is_object(self::$_instance)) {
            self::$_instance = new \LTOC\Tagging();
        }

        return self::$_instance;
    }

    public function activatePlugin()
    {
        foreach ($this->plugin_option_defaults as $option => $default_value) {
            add_option($option, $default_value);
        }

        // delete the old logs
        if(file_exists(LTOC_HISTORICAL_LOG)) {
            unlink(LTOC_HISTORICAL_LOG);
        }

        if(file_exists(LTOC_PROCESS_FILE)) {
            unlink(LTOC_PROCESS_FILE);
        }

        $this->retryTagging();

        // register wp cron job action
        wp_schedule_event(time(), 'hourly', 'ltoc_tagging_event');
    }

    public function deactivatePlugin()
    {
        wp_clear_scheduled_hook('ltoc_tagging_event');
        if(file_exists(LTOC_PROCESS_FILE)) {
            unlink(LTOC_PROCESS_FILE);
        }
    }

    private function actionRoutes()
    {
        // tag posts in batch via url
        $this->actionRoute(
            'ltoc-batch-tagging',
            array($this, 'batchTagging'),
            array(isset($_REQUEST['posts']) ? (int)$_REQUEST['posts'] : $this->batch_posts)
        );
        $this->actionRoute('ltoc-untagged-posts', array($this, 'numberOfUntaggedPosts'));
        $this->actionRoute('ltoc-log-output', array($this, 'getLogOutput'));
    }

    private function addActions()
    {
        add_action('admin_menu', array($this, 'addOptionsPage'));

        if ($this->add_tag_on_save === 'on') {
            add_action('post_updated', array($this, 'tagPost'), 10, 3);
        }
    }

    private function actionRoute($action_name, $action, $action_params = array(), $exit = true)
    {
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == $action_name) {
            if (is_array($action)) {
                call_user_func_array($action, $action_params);
            } else {
                call_user_func($action);
            }
            if ($exit) {
                exit;
            }

        }
    }

    private function getTags($text)
    {
        // skip if api key is not defined
        if (!$this->api_key) {
            return array();
        }

        try {
            $ltoc = new OpenCalais($this->api_key);
            $tags = $ltoc->getEntities($text, $this->relevance / 100, true);
        } catch (OpenCalaisException $e) {
            return array('result' => 'exception', 'message' => $e->getMessage());
        }
        if (empty($tags)) {
            return array('result' => 'no tags');
        }

        return $tags;
    }

    private function loadPluginOptions()
    {
        $api_key = get_option('ltoc_api_key');
        $this->relevance = get_option('ltoc_tag_relevance');
        $this->add_tag_on_save = get_option('ltoc_add_tag_on_save');
        $this->batch_posts = get_option('ltoc_batch_posts');
        $this->included_categories = get_option('ltoc_included_categories');
        $this->disable_batch = get_option('ltoc_disable_batch');
        if (empty($api_key)) {
            add_action('admin_notices', array($this, 'showMissingApiKeyNotice'));
        } else {
            $this->api_key = $api_key;
        }
        $blacklist = get_option('ltoc_tag_blacklist');
        if(strlen($blacklist) > 0) {
            $blacklist_array = explode("\n",$blacklist);
            if(count($blacklist_array) > 0) {
                $this->blacklist = $blacklist_array;
            }
        }
    }

    public function addOptionsPage()
    {
        add_menu_page(
            'Laiser Tag Plugin',
            'Laiser Tag',
            'manage_options',
            LTOC_PLUGIN_NAME,
            array($this, 'addOptionsPageFields'),
            'dashicons-admin-page'
        );

        add_submenu_page(
            LTOC_PLUGIN_NAME,
            'Tagging',
            'Tagging',
            'manage_options',
            LTOC_PLUGIN_NAME,
            array($this, 'addOptionsPageFields')
        );
    }

    public function addOptionsPageFields()
    {
        if(isset($_REQUEST['ltoc_submit'])) {
            $params = [
                'ltoc_api_key',
                'ltoc_included_categories',
                'ltoc_batch_posts',
                'ltoc_tag_relevance',
                'ltoc_add_tag_on_save',
                'ltoc_disable_batch',
                'ltoc_tag_blacklist'
            ];
            foreach ($params as $p) {
                if(isset($_REQUEST[$p])) {
                    update_option($p, $_REQUEST[$p]);
                }
                else {
                    if($p == "ltoc_add_tag_on_save" || $p = "ltoc_disable_batch") {
                        update_option($p, "");
                    }
                }
            }
        }

        // load styles and scripts
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('adminOptionsPageScript',
            LTOC_ASSETS_URL . 'js/adminOptionsPageScript.js',
            array('jquery-ui-slider'),
            LTOC_PLUGIN_VERSION);
        wp_enqueue_style('jquery-ui-slider-css',
            'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css',
            false,
            LTOC_PLUGIN_VERSION);
        wp_enqueue_style('adminOptionsPageStyles',
            LTOC_ASSETS_URL . 'css/adminOptionsPageStyles.css',
            array('jquery-ui-slider-css'),
            LTOC_PLUGIN_VERSION);

        include LTOC_TEMPLATES.'adminOptionPage.php';
    }

    public function showMissingApiKeyNotice() {
        if(isset($_REQUEST['page']) && $_REQUEST['page'] == LTOC_PLUGIN_NAME) {
            return;
        }
        include LTOC_TEMPLATES.'missingApiAdminNotice.php';
    }

    public function tagPost($post_id, $post, $post_before)
    {
        if($post->post_status == 'trash') {
            return 'trash';
        }

        // If this is just a revision, don't update tags
        if (wp_is_post_revision($post_id)) {
            return 'revision';
        }

        // skip if api key is not defined
        if (!$this->api_key) {
            return 'no key';
        }

        // check the category selection
        $terms = wp_get_post_categories( $post->ID, array( 'orderby' => 'parent', 'order' => 'DESC' ) );
        if ( ! empty( $terms ) ) {
            foreach ($terms as $t) {
                $root_cat_id = $this->getTopLevelCategory($t);
                if(!empty($this->included_categories) && !in_array($root_cat_id, $this->included_categories)) {
                    update_post_meta($post_id, 'ltoc_tagged', self::$STATUS_EXCLUDED_CATEGORY);
                    return 'category not included';
                }
            }
        }

        // make sure the logger file exists
        touch(LTOC_BATCH_LOG);

        $post->post_content = strip_tags($post->post_content);

        // check if content is greater than Open Calais' 100KB limit
        $limit = 1000 * 1000;
        if(mb_strlen($post->post_content, '8bit') > $limit) {
            $post->post_content = mb_strcut($post->post_content, 0, $limit -1);
        }

        $tags = $this->getTags(strip_tags($post->post_content));

        //check if non-tag result returned
        if(isset($tags['result'])) {
            if($tags['result'] == 'no tags') {
                $this->batchLog("No tags found for post [". $post->post_title ."]");
                update_post_meta($post_id, 'ltoc_tagged', self::$STATUS_NO_TAGS_FOUND);
                return 'no tags';
            }
            if($tags['result'] == 'exception') {
                $this->batchLog("OpenCalaisException [". $post->post_title ."] :: [".$tags['message']."]");
                update_post_meta($post_id, 'ltoc_tagged', self::$STATUS_OC_ERROR);
                return 'exception';
            }
            return 'non-tag result';
        }

        // update tags for post
        if (!empty($tags)) {
            foreach ($tags as $idx => $t) {
                if(strpos($t, 'Draft:') !== false) {
                    $tags[$idx] = str_replace('Draft:', '', $t);
                }
            }
            if($this->blacklist == "") {
                $this->blacklist = [];
            }
            foreach ($tags as $idx => $t) {
                foreach ($this->blacklist as $b) {
                    $b = trim($b);
                    if($t == $b) {
                        unset($tags[$idx]);
                    }
                }
            }
            wp_set_post_tags($post_id, $tags, true);
            $post_tags = wp_get_post_tags($post_id);
            if(empty($post_tags)) {
                $error_message = "ERROR! [". $post->post_title ." - ID ". $post_id ."] was not tagged! Retry in next batch :: [".implode(', ', $tags)."]";
                $this->errorLog($error_message);
                error_log(date('Y-m-d H:i:s', time())." :: ".$error_message);
                return 'tags not stored';
            }
            else {
                update_post_meta($post_id, 'ltoc_tagged', self::$STATUS_TAGGED);
                return 'tagged';
            }
        }
        return 'done';
    }

    public function batchTagging($batch_posts = FALSE)
    {
        if ($batch_posts === FALSE) {
            $batch_posts = $this->batch_posts;
        }
        set_time_limit(0);

        // file check to stop this cron from running simultaneously
        if($this->isProcessRunning()) {
            echo "in progress";
            die;
        }

        unlink(LTOC_BATCH_LOG);
        touch(LTOC_BATCH_LOG);
        $this->batchLog("Starting batch process...");

        if($this->disable_batch == 'on') {
            $this->batchLog("Batch tagging disabled");
            unlink(LTOC_PROCESS_FILE);
            die;
        }

        require_once ABSPATH . WPINC .'/pluggable.php';
        $args = array(
            'posts_per_page' => $batch_posts,
            'orderby' => 'post_date',
            'order' => 'ASC',
            'post_type' => 'post',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'ltoc_tagged',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $tagged = 0;
        $exception = 0;
        $no_tags = 0;
        $tags_not_stored = 0;

        $untagged_posts = new \WP_Query($args);

        foreach ($untagged_posts->posts as $post) {
            $result = $this->tagPost($post->ID, $post, $post);
            if($result === 'tagged') {
                $tagged++;
            }
            if($result === 'exception') {
                $exception++;
            }
            if($result === 'no tags') {
                $no_tags++;
            }
            if($result === 'tags not stored') {
                $tags_not_stored++;
            }
            sleep(2);
        }
        // unlink the process file, saves us having to check for the process later
        unlink(LTOC_PROCESS_FILE);
        $finalresult = "$tagged posts tagged successfully.";
        if($exception > 0) {
            $finalresult .= " $exception posts returned Open Calais API errors.";
        }
        if($no_tags > 0) {
            $finalresult .= " $no_tags posts had no tags found.";
        }
        if($tags_not_stored > 0) {
            $finalresult .= " $tags_not_stored posts returned tags but were not stored.";
        }
        $this->batchLog("Batch process completed. $finalresult");
    }

    public function numberOfUntaggedPosts()
    {
        require_once ABSPATH . WPINC .'/pluggable.php';

        $args = array(
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'post_type' => 'post',
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'ltoc_tagged',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        );

        $untagged_posts_obj = new \WP_Query($args);
        $untagged_posts = count($untagged_posts_obj->posts);

        echo $untagged_posts;
        return $untagged_posts;
    }

    public function getLogOutput()
    {
        require_once ABSPATH . 'wp-includes/pluggable.php';

        touch(LTOC_BATCH_LOG);

        echo file_get_contents(LTOC_BATCH_LOG);
        return file_get_contents(LTOC_BATCH_LOG);
    }

    public function retryTagging() {
        require_once ABSPATH . 'wp-includes/pluggable.php';

        global $wpdb;

        $query = "delete from $wpdb->postmeta where post_id in (
select distinct wp.ID from $wpdb->posts wp where wp.ID not in 
	(select distinct object_id from $wpdb->term_relationships where term_taxonomy_id in 
		(select term_id from $wpdb->term_taxonomy wt where wt.`taxonomy` = 'post_tag')
	) 
	and wp.post_status = 'publish' and wp.post_type = 'post' order by post_date desc
) and meta_key = 'ltoc_tagged'";

        $wpdb->query($query);

        return "retry ready";
    }

    public function loadSitemap() {
        global $wp_query;
        // if this is not a request for the sitemap or a singular object then bail
        if ( ! isset( $wp_query->query_vars['category_name'] ) || $wp_query->query_vars['category_name'] != 'laiser-tag-sitemap.xml' )
            return;

        if(empty($_GET)) {
            header("Content-type: text/xml");
            http_response_code(200);
            echo $this->compileSitemapIndex();
            exit;
        }

        if(!isset($_GET['page'])) {
            // just exist out, something is wrong
            die;
        }

        header("Content-type: text/xml");
        http_response_code(200);
        echo $this->compileSitemap($_GET['page']);
        exit;
    }

    public function batchLog($message) {
        file_put_contents(LTOC_BATCH_LOG, date('Y-m-d H:i:s', time())." :: $message\n", FILE_APPEND);
    }

    public function errorLog($message) {
        file_put_contents(LTOC_ERROR_LOG, date('Y-m-d H:i:s', time())." :: $message\n", FILE_APPEND);
    }

    private function compileSitemapIndex()
    {
        global $wpdb;

        $result = $wpdb->get_row("SELECT count(term.term_id) as `count` FROM {$wpdb->prefix}term_taxonomy tax 
LEFT JOIN {$wpdb->prefix}terms term ON term.term_id = tax.term_id WHERE tax.taxonomy = 'post_tag' and tax.count > 0");

        if (!isset($result->count)) {
            // just exit out, something is wrong
            return "";
        }

        $pages = floor($result->count / 10000);

        if ($pages < 1) {
            $pages = 1;
        }

        if($result->count > $pages * 10000) {
            $pages++;
        }

        $string = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
        for ($i = 1; $i <= $pages; $i++) {
            $string .= "<sitemap><loc>" . get_site_url() . "/laiser-tag-sitemap.xml?page=" . $i . "</loc></sitemap>";
        }
        $string .= "</sitemapindex>";
        return $string;
    }

    private function compileSitemap($page) {
        global $wpdb;

        $page = ((int)$page) - 1;
        $limit = 10000;
        $offset = $page * 10000;

        $results = $wpdb->get_results("SELECT term.term_id FROM {$wpdb->prefix}term_taxonomy tax 
LEFT JOIN {$wpdb->prefix}terms term ON term.term_id = tax.term_id WHERE tax.taxonomy = 'post_tag' and tax.count > 0 limit $offset, $limit");

        if (count($results) == 0) {
            // just exit out, something is wrong
            return "";
        }

        $string = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

        foreach ($results as $r) {
            $string .= "<url><loc>".get_tag_link($r->term_id)."</loc></url>";
        }
        $string .= "</urlset>";
        return $string;
    }

    private function getTopLevelCategory($catid)
    {
        $cat_parent_id = 0;
        while ($catid != null & $catid != 0) {
            $current_term = get_term($catid);
            $catid = $current_term->parent;
            if ($catid != null & $catid != 0) {
                $cat_parent_id = $catid;
            } else {
                $cat_parent_id = $current_term->term_id;
            }
        }
        return $cat_parent_id;
    }

    private function isProcessRunning() {
        touch(LTOC_PROCESS_FILE);
        $last_pid = file_get_contents(LTOC_PROCESS_FILE);
        if(!empty($last_pid)) {
            exec('ps aux | grep "'.$last_pid.'"', $output, $result);
            foreach ($output as $line) {
                // compress spaces, then get the PID from the start of the line
                $line = preg_replace('/\s+/', ' ', $line);
                $splits = explode(' ', $line);
                $pid = $splits[1];
                if($pid == $last_pid) {
                    // then there is a process still running with the last recorded PID for this process
                    return true;
                }
            }
        }
        // at this point we're sure there is no other process running
        file_put_contents(LTOC_PROCESS_FILE, getmypid());
        return false;
    }
}
