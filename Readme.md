# arillo/silverstripe-arbitrarysettings

Extends a DataObject with a mutil value field to store arbitrary settings in it.

### Requirements

SilverStripe CMS ^4.0

For a SilverStripe 3.x compatible version of this module, please see the [1 branch, or 0.x release line](https://github.com/arillo/silverstripe-arbitrarysettings/tree/1.0).

### Usage

Add and setup the extension on your DataObject

e.g. in config.yml:

```yml
MyDataObject:
  extensions:
    # adds a field called ArbitrarySettings
    - Arillo\ArbitrarySettings\SettingsExtension

  # define your settings
  settings:
    show_title:
      options:
        0: "No"
        1: "Yes"
      default: 0
      label: "Show title as image caption?"
    image_alignment:
      options:
        "left": "Left"
        "right": "Right"
      default: "left"
      label: "Image alignment"
```

**Note:** All keys should be alphanumeric (including underscores, haven't tested other special characters yet) and should not contain whitespace.

To add the field in CMS you can use a helper method to show the field:

```php
use Arillo\ArbitrarySettings\SettingsExtension;

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

`SettingsField` has functions available to manipulate the source of the field:

For including or excluding certain setting you can use:

```php
// will show all settings but show_title
$settingsField->exclude(['show_title']);

// will show show_title setting only
$settingsField->include(['show_title']);
```

It is also possible to update the default value for a setting (for sure only if its present as an option):

```php
$settingsField->updateDefaultForKey('show_title', 1);
```


### Settings presets

It is possible to define a list of setting presets like this:

```yml
Arillo\ArbitrarySettings\SettingsExtension:
  presets:
    bg:
      options:
        transparent: "Transparent"
        light: "Light blue"
      default: transparent
      label: "Background color"
    imgType:
      options:
        Default: "Default image"
        Hero: "Hero image"
      default: Default
      label: "Image type"
```

With these presets defined it is possible to reference these keys in your DataObject's settings config, e.g.:

```yml
MyDataObject:
  extensions:
    - Arillo\ArbitrarySettings\SettingsExtension

  # define your settings
  settings:
    - bg
    - imgType
```

### Translations

To translate the form field label used by `SettingsExtension::field_for` can be changed like this:

```yml
en:
  Arillo\ArbitrarySettings\SettingsExtension:
    Label: "Options"
```

To translate options follow the following convention:

```yml
# for a config like this:
MyObject:
  settings:
    show_title:
      options:
        0: "No"
        1: "Yes"
      default: 0
      label: "Show title as image caption?"
# the following translation keys can be used:
en:
  MyObject:
    setting_show_title_option_0: "Nope"
    setting_show_title_option_1: "Yep"
    setting_show_title_label: "Use title as image caption"
```
