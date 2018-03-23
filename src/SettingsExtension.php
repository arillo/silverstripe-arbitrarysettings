<?php
namespace Arillo\ArbitrarySettings;

use SilverStripe\ORM\DataExtension;
// use \Config;
use SilverStripe\ORM\DataObject;

/**
 * Extends a DataObject with a mutil value field to store arbitrary settings in it.
 *
 * @package arbitrarysettings
 * @author bumbus@arillo <sf@arillo.net>
 */
class SettingsExtension extends DataExtension
{
    /**
     * DB field to save the settings
     * @var string
     */
    protected $_settingsDBField = null;

    /**
     * Generates a settings field for a given DataObject
     * @param  DataObject $owner
     * @return mixed
     */
    public static function field_for(DataObject $owner)
    {
        $settings = $owner->config()->get('settings');
        // \SilverStripe\Dev\Debug::dump($settings);
        if ($settings && self::valid_settings($settings))
        {
            return SettingsField::create(
                $owner->getSettingsDBField(),
                _t(__CLASS__ . '.Label', 'Settings'),
                self::translate_settings($owner, $settings)
            );
        }
        return false;
    }

    /**
     * Sanity checks the structure of configuration
     * @param  array  $source
     * @return array
     */
    public static function valid_settings(array $source)
    {
        if (is_array($source))
        {
            // foreach ($source as $key => $settings)
            // {
            //     if (!isset($settings['options']))
            //     {
            //         throw new InvalidArgumentException("Setting [{$key}]: No options defined");
            //     }

            //     if (!isset($settings['default']))
            //     {
            //         throw new InvalidArgumentException("Setting [{$key}]: No default value defined");
            //     }

            //     if (!isset($settings['options'][$settings['default']]))
            //     {
            //         throw new InvalidArgumentException("Setting [{$key}]: Default value '{$settings['default']}' cannot be found in the options");
            //     }
            // }

            return $source;
        }
        // throw new InvalidArgumentException("Settings source should be an array");
    }

    /**
     * Translates settings
     * @param  DataObject $owner
     * @param  array      $source
     * @return array
     */
    public static function translate_settings(DataObject $owner, array $source)
    {
        foreach ($source as $key => $settings) {
            if (isset($settings['options']))
            {
                foreach ($settings['options'] as $option => $label)
                {
                    $source[$key]['options'][$option] = _t("{$owner->class}.setting_{$key}_option_{$option}", $label);
                }
            }
            $source[$key]['label'] = _t("{$owner->class}.setting_{$key}_label", $source[$key]['label']);
        }
        return $source;
    }

    /**
     * @param string $settingsDBField   the name of db field to save in
     */
    public function __construct($settingsDBField = 'ArbitrarySettings')
    {
        parent::__construct();
        $this->_settingsDBField = $settingsDBField;
    }

    public function onBeforeWrite()
    {
        $dbField = "{$this->_settingsDBField}Value";
        $this->owner->$dbField = $this->owner->{$this->_settingsDBField};
        parent::onBeforeWrite();
    }

    /**
     * Add db field for the settings
     * @param  string $class
     * @param  string $extension
     * @return array
     */
    public function extraStatics($class = null, $extension = null)
    {
        return [
            'db' => [ $this->_settingsDBField => 'MultiValueField' ]
        ];
    }

    /**
     * Returns a setting by a name.
     * With active $returnDefault flag it returns the default value from config, in case there is no value stored in DB.
     *
     * @param string  $name
     * @param boolean $returnDefault
     */
    public function SettingByName($name = null, $returnDefault = true)
    {
        $settings = $this->owner->{$this->_settingsDBField}->getValue();

        if ($settings
            && is_array($settings)
            && is_string($name)
            && isset($settings[$name])
        ) {
            return $settings[$name];
        }

        if (filter_var($returnDefault, FILTER_VALIDATE_BOOLEAN))
        {
            $config = $this->owner->config()->get('settings');
            if (isset($config[$name]) && isset($config[$name]['default']))
            {
                return $config[$name]['default'];
            }
        }
        return false;
    }

    public function getSettingsDBField()
    {
        return $this->_settingsDBField;
    }
}
