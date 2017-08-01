# arillo/silverstripe-arbitrarysettings

Extends a DataObject with a mutil value field to store arbitrary settings in it.

### Usage

Add and setup the extension on your DataObject

e.g. in config.yml:

```yml
MyDataObject:
  extensions:
     # adds a field called ArbitrarySettings (default)
     - arillo\arbitrarysettings\SettingsExtension

     # or adds a field called 'MySettings'
     - arillo\arbitrarysettings\SettingsExtension("MySettings")

  # define your settings
  settings:
    show_title:
      options:
        0: 'No'
        1: 'Yes'
      default: 0
      label: 'Show title as image caption?'
    image_alignment:
      options:
        'left': 'Left'
        'right': 'Right'
      default: 'left'
      label: 'Image alignment'
```

**Note:** All keys should be alphanumeric (including underscores, haven't tested other special characters yet) and should not contain whitespace.

To add the field in CMS you can use a helper method to show the field:

```php
use arillo\arbitrarysettings\SettingsExtension;

public function getCMSFields()
{
    $fields = parent::getCMSFields();
    if ($settingsField = SettingsExtension::field_for($this))
    {
        $fields->addFieldToTab('Root.Main', $settingsField);
    }
    return $fields;
}
```

Values can be accessed like this:

```php
$this->SettingByName('image_alignment') // returns 'left' or 'right'
```

in templates:

```html
<div class="$SettingByName(image_alignment)">...</div>
```

### Translations

To translate the form field label used by `SettingsExtension::field_for` can be changed like this:

```yml
en:
  arillo\arbitrarysettings\SettingsExtension:
    Label: 'Options'
```

To translate options follow the following convention:

```yml
# for a config like this:
MyObject:
  settings:
    show_title:
      options:
        0: 'No'
        1: 'Yes'
      default: 0
      label: 'Show title as image caption?'
# the following translation keys can be used:
en:
  MyObject:
    setting_show_title_option_0: 'Nope'
    setting_show_title_option_1: 'Yep'
    setting_show_title_label: 'Use title as image caption'
```


