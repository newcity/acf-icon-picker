# ACF Icon Selector Field

Allows you to create an 'nc-acf-icon-picker' acf-field.

---

## Description

Add the svg icons you want to be available in your theme to an acf folder inside an img folder in your theme.

## Compatibility

This plugin has been tested with WordPress version 5.0 - 6.3.1

## Installation

1. Copy the `acf-icon-picker` folder into your `wp-content/plugins` folder
2. Activate the Icon Selector plugin via the plugins admin page
3. Create a new field via ACF and select the NC Icon Picker type

## Adding Icons

By default, this plugin looks for SVG files in your theme's `images/svg-icons/` directory.
The name of each icon shown in the picker will be the file name of its SVG file without the
file's `.svg` extension. Icons will always be listed in alphabetical order.

## Retrieving an Icon Value

When you retrieve the value of an `nc-acf-icon-picker` field using `get_field()`, the result
is a JSON-encoded array with values for `icon` and `path`. You will be required to write your own
code to convert those values to something useful to your context. For example, if your result
from `get_field()` was the following (formatted for readability):

```json
{
  "icon": "apple",
  "path": "images/svg-icons/"
}
```

You could create an image element by converting the JSON to a PHP array and combining the values into a `src` attribute:

```php
// This is not production code — proper code would include checks for valid JSON,
// existing array keys, etc. It would also include, at the very least, an `alt` attribute on the image.
$icon_value = json_decode( $raw_icon_value_from_acf );
$icon_src = get_template_directory_uri() . '/' . $icon_value['path'] . $icon_value['icon'] . '.svg';
?>
<img src="<?php echo $icon_src; ?>" />

```

## Parent / Child Theme Icons

If you are using a parent theme and one or more child themes, you can have a shared icon set in the parent theme
and then override or supplement that set for each child theme. Icons in the parent theme's
`images/svg-icons/` directory will be shared by all child themes. Icons in the child theme's
`images/svg-icons/` directory will only be used for that specific child theme. If an icon with exactly the
same file name is found in both the child theme and the parent theme, the child theme's version will be used.

## Filters

### Changing the Theme's Icon Directory

Use the `nc_acf_icon_path_suffix` hook in your theme or plugin to override the default icon directory.
Your filter function should return a path relative to the current theme directory.

```
add_filter( 'nc_acf_icon_path_suffix', 'nc_acf_icon_path_suffix' );

function nc_acf_icon_path_suffix( $path_suffix ) {
    return 'assets/icons/';
}
```

This filter hook changes the icon for both the currently activated theme and the parent theme,
if you are using one. To change the icon directory path for the parent theme separately, use the
`nc_acf_icon_parent_path_suffix` hook as well.

### Setting the Output Format

This field saves and returns its as a JSON-encoded associative array, with `icon` and `path` values. By default, it will return the saved value without formatting it when you retrieve it using `get_field()`, even if the `$format_value` argument is set to `true`.

You can use the `nc_acf_icon_picker_format_type` filter to globally change this behavior for all ACF Icon Selector fields. To use the filter, add a callback function that returns a string.

```php
// Example for using the `nc_acf_icon_picker_format_type` filter
// to set all ACF Icon Selector fields to return PHP arrays.
add_filter( 'nc_acf_icon_picker_format_type' , function () {
  return 'array';
} );
```

Here are the available format types:

- `null`: The default behavior — no formatting
- `"json"`: Returns the saved value as a JSON-formatted string. For the difference between `"json"` and `null`, see the "note on legacy value support" below.
- `"array"`: Converts the JSON string into a PHP associative array.
- `"string"`: Returns only the `icon` property from the array.

**Note:** If you use `get_field()` with `$format_value` set to `false`, this filter will be ignored.

**Note on legacy value support**  
You can use this field type to replace an existing field that stores the icon name as a basic string. When you set one of the format types other than `null`, the formatter will check to see if the saved value is a plain string instead of JSON. If so, it will convert that value to match the chosed format type. For example, for the `array` format type, a plain string value will be converted into an array with that string value set as the `icon` property.

This conversion is not backwards-compatible. Any icon values saved using this plugin will be permanently saved as JSON, even if you are re-saving a field that was formerly a plain string value.

## Changelog

- 2.1.1 - Fixed bug where existing string values were not returned correctly with the `string` formatter.

- 2.1.0 - Added `nc_acf_icon_picker_format_type` filter. For details, see the ReadMe under "Filters > Setting the Output Format"

- 2.0.1 - Added a deprecation notice for the old `acf_icon_path_suffix` filter hook. This hook was already deprecated, but the change was not documented and no warning was provided. Please update all references from `acf_icon_path_suffix` to `nc_acf_icon_path_suffix`.

- 2.0.0 - **Major Update.** Before updating your copy of the plugin, review the following changes.

  - Changed the field type name from `icon-picker` to `nc-acf-icon-picker` and updated its label from "Icon Picker" to "NC Icon Picker". Anywhere that you have
    set up an icon picker field using this plugin, you must change the field's `type` value to `nc-acf-icon-picker`. If a field group is stored in code as JSON or PHP, you can make this change by changing the `type` value directly. If you are managing the field group directly within the ACF settings in WordPress, you will need to change the type to "NC Icon Picker".
  - Changed the default path to icon files to `images/svg-icons/`. If you were using the old version of the plugin without modifying the path, you need to use the `nc_acf_icon_path_suffix` hook in your theme to change the path back to its old value (`assets/img/acf/`)
  - Introduced parent/child theme support. Consult the "Parent / Child Theme Icons" section of this ReadMe for details on how to use this new feature.

- 1.6.0 - Performance fix with lots of icons. Thanks to ![idflood](https://github.com/houke/acf-icon-picker/pull/9)
- 1.5.0 - Fix issue where searching for icons would break preview if icon name has space
- 1.4.0 - Add filter to change folder where svg icons are stored
- 1.3.0 - Adding close option on modal
- 1.2.0 - Adding search filter input to filter through icons by name
- 1.1.0 - Add button to remove the selected icon when the field is not required
- 1.0.0 - First release
