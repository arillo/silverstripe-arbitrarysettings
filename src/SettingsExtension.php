<?php
namespace Arillo\ArbitrarySettings;

use InvalidArgumentException;
use SilverStripe\Core\Config\Config;
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

    private static $db = [
        'ArbitrarySettings' => MultiValueField::class,
    ];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->owner->ArbitrarySettingsValue) {
            $settings = json_decode($this->owner->ArbitrarySettingsValue, true);

            if (SettingsField::is_unformatted_value($settings)) {
                $this->owner->ArbitrarySettingsValue = json_encode(
                    SettingsField::format_value($settings)
                );
            }
        }
    }

    /**
     * Generates a settings field for a given DataObject.
     *
     * @param  DataObject $owner
     * @return mixed
     */
    public static function field_for(DataObject $owner)
    {
        $settings = $owner->config()->get('settings') ?? [];
        $settings = self::normalize_settings($settings);

        if (self::valid_settings($settings)) {
            return SettingsField::create(
                self::DB_FIELD,
                _t(__CLASS__ . '.Label', 'Settings'),
                self::translate_settings($owner, $settings)
            );
        }
        return false;
    }

    /**
     * If settings is a sequential array, it checks in list of presets for the key
     * and appends its options to the settings.
     *
     * @param  array  $settings
     * @return array
     */
    public static function normalize_settings($settings)
    {
        if (!is_array($settings)) {
            return null;
        }

        if (array_keys($settings) !== range(0, count($settings) - 1)) {
            return $settings;
        }

        $newSettings = [];
        if ($presets = Config::inst()->get(__CLASS__, 'presets')) {
            foreach ($settings as $key) {
                if (isset($presets[$key])) {
                    $newSettings[$key] = $presets[$key];
                }
            }

            return $newSettings;
        }

        return $settings;
    }

    /**
     * Sanity checks the structure of configuration
     * @param  array  $source
     * @return array
     */
    public static function valid_settings(array $source)
    {
        if (is_array($source)) {
            foreach ($source as $key => $settings) {
                if (!isset($settings['options'])) {
                    throw new InvalidArgumentException(
                        "Setting [{$key}]: No options defined"
                    );
                }

                if (!isset($settings['default'])) {
                    throw new InvalidArgumentException(
                        "Setting [{$key}]: No default value defined"
                    );
                }

                if (!isset($settings['options'][$settings['default']])) {
                    throw new InvalidArgumentException(
                        "Setting [{$key}]: Default value '{$settings['default']}' cannot be found in the options"
                    );
                }
            }

            return $source;
        }
        throw new InvalidArgumentException(
            'Settings source should be an array'
        );
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
            if (isset($settings['options'])) {
                foreach ($settings['options'] as $option => $label) {
                    $source[$key]['options'][$option] = _t(
                        get_class($owner) . ".setting_{$key}_option_{$option}",
                        $label
                    );
                }
            }
            $source[$key]['label'] = _t(
                get_class($owner) . ".setting_{$key}_label",
                $source[$key]['label']
            );
            if (isset($source[$key]['description'])) {
                $source[$key]['description'] = _t(
                    get_class($owner) . ".setting_{$key}_description",
                    $source[$key]['description']
                );
            }
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
        $settings = is_array($this->owner->{self::DB_FIELD})
            ? $this->owner->{self::DB_FIELD}
            : $this->owner->{self::DB_FIELD}->getValue();

        if (
            $settings &&
            is_array($settings) &&
            is_string($name) &&
            isset($settings[$name])
        ) {
            return $settings[$name];
        }

        if (filter_var($returnDefault, FILTER_VALIDATE_BOOLEAN)) {
            $config = self::normalize_settings(
                $this->owner->config()->get('settings')
            );

            if (
                $config &&
                isset($config[$name]) &&
                isset($config[$name]['default'])
            ) {
                return $config[$name]['default'];
            }
        }
        return false;
    }
}
