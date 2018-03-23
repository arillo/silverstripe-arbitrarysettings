<?php
namespace Arillo\ArbitrarySettings;

use \MultiValueTextField;
use \Requirements;

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
    protected $_source;

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
    public function __construct($name, $title = null, $source = [], $value = null, $form = null) {
        $this->_source = $source;
        parent::__construct($name, ($title === null) ? $name : $title, $value, $form);
    }

    public function Field($properties = [])
    {
        Requirements::javascript(ARBITRARYSETTINGS_DIR . '/js/settingsfield.js');
        Requirements::css(ARBITRARYSETTINGS_DIR . '/css/settingsfield.css');
        $nameKey = $this->name . '[key][]';
        $nameVal = $this->name . '[val][]';
        $fields = [];

        $source = htmlspecialchars(json_encode([
            'formId' => $this->id(),
            'keyName' => $this->name . '[key][]',
            'valueName' => $this->name . '[val][]',
            'options' => $this->getSource()
        ]), ENT_QUOTES, 'UTF-8');

        $html = '<ul id="'. $this->id() .'" class="multivaluefieldlist arbitrarysettingslist '. $this->extraClass().'" data-source="' . $source . '"></ul>';
        return $html;
    }

    /**
     * Combined settings from config and DB value.
     * @return array
     */
    public function getSource()
    {
        $source = [];
        foreach ($this->_source as $key => $data)
        {
            $data['currentValue'] = $data['default'];
            if ($this->value)
            {
                $source[$key] = $data;
                if (isset($this->value[$key]))
                {
                    $data['currentValue'] = $this->value[$key];
                }
            }
            $source[$key] = $data;
        }
        return $source;
    }

    public function setValue($v)
    {
        if (is_array($v))
        {
            if (isset($v['key']))
            {
                $newVal = [];
                for ($i = 0, $c = count($v['key']); $i < $c; $i++)
                {
                    if (strlen($v['key'][$i]) && strlen($v['val'][$i]))
                    {
                        $newVal[$v['key'][$i]] = $v['val'][$i];
                    }
                }
                $v = $newVal;
            }
        }
        if ($v instanceof MultiValueField)
        {
            $v = $v->getValues();
        }

        parent::setValue($v);
    }
}
