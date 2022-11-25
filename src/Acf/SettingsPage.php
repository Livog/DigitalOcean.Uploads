<?php

namespace PrBiggerUploads\Acf;

use StoutLogic\AcfBuilder\FieldsBuilder;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SettingsPage {

    const PR_SITE_DATA_AUTHORIZATION_OPTION_NAME = 'prSiteDataAuthorizationKey';
    const FIELD_DIGITAL_OCEAN_SPACES_KEY = 'digitalOceanSpacesKey';
    const FIELD_DIGITAL_OCEAN_SPACES_SECRET = 'digitalOceanSpacesSecret';
    const FIELD_DIGITAL_OCEAN_SPACES_CONTAINER = 'digitalOceanSpacesContainer';
    const FIELD_DIGITAL_OCEAN_SPACES_LOCATION = 'digitalOceanSpacesLocation';

    public function __construct()
    {
        add_action('acf/init', [$this, 'init']);
    }

    public function init(){
        $this->create();
    }

    public function create()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' 	=> 'Uploads Settings',
                'menu_title'	=> 'Uploads Settings',
                'menu_slug' 	=> 'digitalocean-uploads-settings',
                'capability'	=> 'manage_options',
                'redirect'		=> false
            ]);
            $settingsFields = new FieldsBuilder('digitalocean-uploads-settings', [
                'title' => 'Site Data Settings',
                'style' => 'seamless'
            ]);
            $settingsFields->addTab('General');
            $settingsFields
                ->addMessage('Authorization Key', get_option(self::PR_SITE_DATA_AUTHORIZATION_OPTION_NAME), [
                    'instructions' => 'This is set when activating the plugin.',
                ]);
            $settingsFields->addTab('DigitalOcean Spaces');
            $settingsFields
                ->addText(self::FIELD_DIGITAL_OCEAN_SPACES_KEY)
                ->addText(self::FIELD_DIGITAL_OCEAN_SPACES_SECRET)
                ->addText(self::FIELD_DIGITAL_OCEAN_SPACES_CONTAINER)
                ->addSelect(self::FIELD_DIGITAL_OCEAN_SPACES_LOCATION, [
                    'choices' => [
                        'ams3'
                    ]
                ]);


            $settingsFields->setLocation('options_page', '==', 'digitalocean-uploads-settings');
            acf_add_local_field_group($settingsFields->build());
        }
    }
}