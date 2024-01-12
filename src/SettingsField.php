<?php
namespace Arillo\ArbitrarySettings;

use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use Symbiote\MultiValueField\Fields\MultiValueTextField;
use SilverStripe\View\Requirements;
use Symbiote\MultiValueField\ORM\FieldType\MultiValueField;

/**
 * @package arbitrarysettings
 * @author @arillo <sf@arillo.net>
 */
class SettingsField extends MultiValueTextField
{
    /**
     * Option source
     * @var array
     */
    protected $source;

    public static function is_unformatted_value($value)
    {
        return is_array($value) && isset($value['key']);
    }

    public static function format_value($value)
    {
        $newVal = [];
        for ($i = 0, $c = count($value['key']); $i < $c; $i++) {
            if (strlen($value['key'][$i]) && strlen($value['val'][$i])) {
                $newVal[$value['key'][$i]] = $value['val'][$i];
            }
        }
        return $newVal;
    }

    /**
     * Constructs the field as usual.
     *
     * $source needs to follow a structure like this:
     * [
     *     '<settings_key>' => [
     *         'options' => [
     *             '<option_key_1>' => '<option label 1>',
     *             '<option_key_2>' => '<option label 2>',
     *             ...
     *             ..
     *             .
     *            'default' => '<one of the option keys, e.g. option_key_1>',
     *            'label' => '<label of this option>'
     *         ]
     *     ],
     *     ...
     *     ..
     *     .
     * ]
     *
     * @param string $name
     * @param string $title
     * @param array  $source
     * @param mixed  $value
     * @param Form $form
     */
    public function __construct(
        $name,
        $title = null,
        $source = [],
        $value = null,
        $form = null
    ) {
        $this->source = $source;
        parent::__construct(
            $name,
            $title === null ? $name : $title,
            $value,
            $form
        );
    }

    public function Field($properties = [])
    {
        Requirements::css(
            'arillo/silverstripe-arbitrarysettings: client/css/settingsfield.css'
        );

        return $this->renderWith('SettingsField', [
            'FormId' => $this->id(),
            'KeyName' => $this->name . '[key][]',
            'ValueName' => $this->name . '[val][]',
            'Settings' => $this->getSettings(),
        ]);
    }

    /**
     * Exclude options by keys.
     *
     * @param  array  $excludes
     * @return SettingsField
     */
    public function exclude(array $excludes = [])
    {
        $source = $this->source;
        foreach ($excludes as $exclude) {
            unset($source[$exclude]);
        }

        $this->source = $source;
        return $this;
    }

    /**
     * Include options by keys.
     *
     * @param  array  $excludes
     * @return SettingsField
     */
    public function include(array $includes = [])
    {
        $source = [];
        foreach ($includes as $include) {
            if (isset($this->source[$include])) {
                $source[$include] = $this->source[$include];
            }
        }

        $this->source = $source;
        return $this;
    }

    /**
     * Update the default for a given key
     *
     * @param string $key
     * @param string $newDefault
     * @return SettingsField
     */
    public function updateDefaultForKey(string $key, string $newDefault)
    {
        $source = $this->source;

        if (
            isset($source[$key]) &&
            isset($source[$key]['options'][$newDefault])
        ) {
            $source[$key]['default'] = $newDefault;
            $this->source = $source;
        }

        return $this;
    }

    public function getSettings()
    {
        $source = ArrayList::create([]);
        foreach ($this->source as $key => $data) {
            $opts = ArrayList::create([]);
            foreach ($data['options'] as $val => $label) {
                $selected = $data['default'] === $val;

                if ($this->value) {
                    if (isset($this->value[$key])) {
                        $selected = $this->value[$key] === $val;
                    }
                }

                $opts->push(
                    ArrayData::create([
                        'Val' => $val,
                        'Label' => $label,
                        'Selected' => $selected,
                    ])
                );
            }

            $source->push(
                ArrayData::create([
                    'Key' => $key,
                    'Label' => $data['label'],
                    'Description' => $data['description'] ?? null,
                    'Options' => $opts,
                ])
            );
        }

        return $source;
    }

    public function setValue($v, $data = null)
    {
        if (self::is_unformatted_value($v)) {
            $v = self::format_value($v);
        }
        if ($v instanceof MultiValueField) {
            $v = $v->getValues();
        }

        parent::setValue($v);
    }
}
