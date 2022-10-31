<?php

namespace unionco\cloudconvert;

use Craft;
use craft\base\Plugin as CraftPlugin;
use unionco\cloudconvert\models\Settings;
use unionco\cloudconvert\services\CloudConvert;

class Plugin extends CraftPlugin
{
    public static Plugin $plugin;
    public $hasCpSettings = true;
    public $schemaVersion = "1.0.0";

    public function init()
    {
        self::$plugin = $this;
        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'unionco\\cloudconvert\\console\\controllers';
        } else {
            $this->controllerNamespace = 'unionco\\cloudconvert\\controllers';
        }

        parent::init();

        $this->setComponents([
            'cloudconvert' => CloudConvert::class,
        ]);
    }

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }
}
