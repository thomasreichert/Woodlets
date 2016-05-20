<?php

namespace Neochic\Woodlets;

class PageConfigurationManager
{
    protected $wpWrapper;
    protected $formManager;
    protected $templateManager;

    public function __construct($wpWrapper, $formManager, $templateManager) {
        $this->wpWrapper = $wpWrapper;
        $this->formManager = $formManager;
        $this->templateManager = $templateManager;
    }

    public function addMetaBoxes() {
        $config = $this->templateManager->getConfiguration();
        $data = $this->wpWrapper->getPostMeta();
        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        foreach($config['forms'] as $key => $section) {
            $this->wpWrapper->addMetaBox('page_section_' . $key, $section['title'], function () use ($data, $section) {
                $this->formManager->form($section['config']->getConfig(), $data['data'], function($name) {
                    return array(
                        "id" => 'woodlets_page_setting_' . $name,
                        "name" => 'woodlets_page_settings['. $name . ']'
                    );
                });
            }, ['page', 'post'], 'normal', 'core');
        }
    }

    public function save() {
        if (!isset($_POST["woodlets_page_settings"])) {
            return;
        }

        $config = $this->templateManager->getConfiguration();
        $data = $this->wpWrapper->getPostMeta();
        if (!isset($data['data'])) {
            $data['data'] = array();
        }

        foreach($config['forms'] as $key => $section) {
            $data['data'] = $this->formManager->update($section['config']->getConfig(), $_POST['woodlets_page_settings'], $data['data']);
        }
        
        $this->wpWrapper->setPostMeta($data);
    }
}
?>
