<?php

namespace Neochic\Woodlets;

class WordPressWrapper
{
    protected $dataKey;

    public function __construct($dataKey) {
        $this->dataKey = $dataKey;
    }

    public function addFilter($tag, $function_to_add, $priority = 90) {
        return call_user_func_array('add_filter', func_get_args());
    }

    public function applyFilters() {
        $args = func_get_args();
        $args[0] = 'neochic_woodlets_'.$args[0];
        return call_user_func_array('apply_filters', $args);
    }

    public function addAction($tag, $function_to_add, $priority = 90, $accepted_args = 1) {
        return call_user_func_array('add_action', func_get_args());
    }

    public function doAction($tag,  $arg = '') {
        $args = func_get_args();
        $args[0] = 'neochic_woodlets_'.$args[0];
        return call_user_func_array('do_action', $args);
    }

    public function getPluginVersion() {
        $plugins = get_plugins();

        if(isset($plugins['woodlets/woodlets.php'])) {
            return $plugins['woodlets/woodlets.php']['Version'];
        }

        return false;
    }

    public function isAllowed() {
        return call_user_func_array('current_user_can', func_get_args());
    }

    public function verifyNonce() {
        return call_user_func_array('wp_verify_nonce', func_get_args());
    }

    public function loadPluginTextdomain() {
        return call_user_func_array('load_plugin_textdomain', func_get_args());
    }

    public function translate($text, $domain = 'default', $parameters = null) {
        $translation = call_user_func_array('translate', func_get_args());

        if (is_array($parameters)) {
            $translation = vsprintf($translation, $parameters);
        }

        return $translation;
    }

    public function getPostMeta($key = null, $postId = null, $useRevision = false) {
        if (!is_admin() && !is_singular() && !$this->inTheLoop()) {
            // do not retrieve meta data for first post on list views
            return null;
        }

        if ($key === null) {
            $key = $this->dataKey;
        }

        if ($postId === null) {
            $postId = $this->getPost() ? $this->getPost()->ID : null;
        }

        if ($useRevision) {
            return get_metadata('post', $postId, $key, true) ?: array();
        }

        return get_post_meta($postId, $key, true) ?: array();
    }

    public function setPostMeta($value, $key = null, $postId = null) {
        if($key === null) {
            $key = $this->dataKey;
        }
        if($postId === null) {
            $postId = $this->getPost()->ID;
        }

        /*
         * if it's a revision save it also to the revision
         */
        if (wp_is_post_revision($postId)) {
            add_metadata('post', $postId, $key, $value);
        }

        return update_post_meta($postId, $key, $value);
    }

    public function getUserMeta($userId, $key = null) {
        return get_user_meta($userId, $key, true) ?: array();
    }

    public function setUserMeta($userId, $value, $key = null) {
        if($key === null) {
            $key = $this->dataKey;
        }

        return update_user_meta($userId, $key, $value);
    }

    public function addStyle($name, $url) {
        wp_register_style('neochic-woodlets-'.$name, $url, array(), $this->getPluginVersion());
        wp_enqueue_style('neochic-woodlets-'.$name);
    }

    public function addScript($name, $url) {
        wp_register_script('neochic-woodlets-'.$name, $url, array(), $this->getPluginVersion());
        wp_enqueue_script('neochic-woodlets-'.$name);
    }

    public function getThemePaths() {
        return array_unique(
                array(
                    get_stylesheet_directory(),
                    get_template_directory()
                )
        );
    }

    public function registerWidget($name, $widget) {
        $GLOBALS['wp_widget_factory']->widgets[$name] = $widget;
    }

    public function addMetaBox($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null) {
        add_meta_box('neochic-woodlets-'.$id, $title, $callback, $screen, $context, $priority, $callback_args);
    }

    public function getWidgets() {
        $widgets = array();
        foreach($GLOBALS['wp_widget_factory']->widgets as $widget) {
            $widgets[$widget->id_base] = $widget;
        }

        return $widgets;
    }

    public function enableThickbox() {
        add_thickbox();
    }
    
    public function enableMediaUploader() {
        wp_enqueue_media();
    }

    public function wpEditor($content, $editor_id, $settings = array()) {
        return wp_editor($content, $editor_id, $settings);
    }

    public function ajaxUrl() {
        return admin_url('admin-ajax.php');
    }

    public function unslash($val) {
        return wp_unslash($val);
    }

    public function slash($val) {
        return wp_slash($val);
    }

    public function mayBeUnserialize($val) {
        return maybe_unserialize($val);
    }

    public function isDebug() {
        return WP_DEBUG ? true : false;
    }

    public function isPage() {
        $post = $this->getPost();
        return $post && $post->post_type === 'page';
    }

    public function inTheLoop() {
        return in_the_loop();
    }

    public function pageNow() {
        if(isset($GLOBALS['pagenow'])) {
            return $GLOBALS['pagenow'];
        }
        return null;
    }

    public function getPostType() {
        $post = $this->getPost();
        if ($post) {
            return $post->post_type;
        }
        return null;
    }

    public function getTemplateType() {
        if (in_the_loop()) {
            return $this->getPost()->post_type;
        }

        if ($this->pageNow() === 'post-new.php') {
            if (isset($_REQUEST['post_type'])) {
                return $_REQUEST['post_type'];
            }
            return "post";
        }

        if ($this->pageNow() === 'post.php') {
            return $this->getPost()->post_type;
        }

        if (is_attachment()) {
            return "attachment";
        }

        if (is_single()) {
            return "post";
        }

        if (is_page()) {
            return "page";
        }

        if (is_404()) {
            return "404";
        }

        if (is_category()) {
            return "category";
        }

        if (is_tag()) {
            return "tag";
        }

        if (is_archive()) {
            return "archive";
        }

        if (is_search()) {
            return "search";
        }

        if (!is_singular()) {
            return "list";
        }

        return null;
    }

    public function wpDie() {
        wp_die();
    }

    public function getAttachment( $attachment_id ) {
        return wp_prepare_attachment_for_js($attachment_id);
    }

    public function getPost($postId = null) {
        if($postId === null) {
            if(isset($GLOBALS['post'])) {
                return $GLOBALS['post'];
            }
            return null;
        }

        return get_post($postId);
    }

    public function getOption($option, $default = false) {
    	return get_option($option, $default);
    }

    public function dateI18n($dateformatstring = null, $unixtimestamp = false, $gmt = false) {
		if($dateformatstring === null) {
			$dateformatstring = $this->getOption('date_format');
		}

		return date_i18n( $dateformatstring, $unixtimestamp, $gmt );
    }
}
