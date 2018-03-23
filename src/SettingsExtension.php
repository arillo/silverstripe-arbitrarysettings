<?php
namespace Arillo\ArbitrarySettings;

use InvalidArgumentException;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

/**
 * Extends a DataObject with a mutil value field to store arbitrary settings in it.
 *
 * @package arbitrarysettings
 * @author bumbus@arillo <sf@arillo.net>
 */
class SettingsExtension extends DataExtension
{
    const DB_FIELD = 'ArbitrarySettings';

    private static
        $db = [
            'ArbitrarySettings' => MultiValueField::class
        ]
    ;

    /**
     * Generates a settings field for a given DataObject.
     *
     * @param  DataObject $owner
     * @return mixed
     */
    public static function field_for(DataObject $owner)
    {
        $settings = $owner->config()->get('settings');

        if ($settings && self::valid_settings($settings))
        {
            return SettingsField::create(
                self::DB_FIELD,
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
            foreach ($source as $key => $settings)
            {
                if (!isset($settings['options']))
                {
                    throw new InvalidArgumentException("Setting [{$key}]: No options defined");
                }

                if (!isset($settings['default']))
                {
                    throw new InvalidArgumentException("Setting [{$key}]: No default value defined");
                }

                if (!isset($settings['options'][$settings['default']]))
                {
                    throw new InvalidArgumentException("Setting [{$key}]: Default value '{$settings['default']}' cannot be found in the options");
                }
            }

            return $source;
        }
        throw new InvalidArgumentException("Settings source should be an array");
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
     * Returns a setting by a name.
     * With active $returnDefault flag it returns the default value from config, in case there is no value stored in DB.
     *
     * @param string  $name
     * @param boolean $returnDefault
     */
    public function SettingByName($name = null, $returnDefault = true)
    {
        $settings = $this->owner->{self::DB_FIELD}->getValue();

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
}
