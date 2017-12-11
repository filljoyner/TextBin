# TextBin Instructions
### The TextBin Tab
In the WordPress admin, a "TextBin" tab will be visible at the bottom of the left navigation. This tab is where you can manage all of your TextBin entries.

To add a new entry, click the "Add New" button at the top of the page. Enter the "Name" of the TextBin Item.

Next add your copy. You can use the editor as you would with any other post content. Images, links, html, etc may all be used.

Select the "Format Text" checkbox if the item will be used in the sidebar or called by the textbin() function and you would like to add paragraphs and line breaks as with any other content area. Leave the box unchecked if you don't want any formatting or if the item will be used in a shortcode. Shortcodes will be added to a copy area and then formatted with the post content.

To Edit an item, click "Edit" on the right side of the item in the list. Next make any changes you would like and click the "Save Item" button.

To reorder your TextBin items, click and drag the up/down arrow on the left side of the item's title to sort them into the order you would like.

To delete a TextBin item, click the "Delete" button on theright side of the item in the list. A box will appear to confirm your deletion. Click "OK" to delete the item or "Cancel" to return to the list without deleting the entry.


### Import/Export
This feature allows you to export your current TextBin items to back them up or import then in another site.Importing TextBin items will not replace your existing items.

#### IMPORTANT
This feature only imports and exports the text information stored in an itemand does not import or export any images, documents, or assets which may be linked to in the text content.


### Using the Widget
The TextBin widget may be added to any widget area multiple times and allows for atitle and TextBin item selection. To use the widget on your widget enabled site, go to the widgets pageof the WordPress admin (Appearance -> Widgets) and look for the "TextBin" widget in the"Available Widgets" box. Click and drag the TextBin widget to any of the widget areas located on the right of the page.
Widget Input Fields:
* Title - The title of the widget area. This is an optional field and, if set, will display above the TextBin item.
* Name - The name of the TextBin item you would like to appear in the widget area.


### Using the Shortcode
With the shortcode, you may add a TextBin entry directly into a copy area in WordPress. A shortcode for an item called "Copyright" would look like:
```
[textbin "Copyright"]
```

If you have more than one entry for a name, you can add an additional setting to the shortcode to return all items and place them into an unordered list:
```
[textbin "Books" single=false]
```


### Using the Function
The TextBin function may be used by theme developers to add items into a theme. The function has 3 parameters. The first is the name of the TextBin item:
```php
textbin('Copyright'); // Echo an item with the name of "Copyright."
```

The second parameter is the single (true) or multiple (false) boolean setting. By default it is set to true:
```php
textbin('Books', false);
```

Returns an array of all entries named "Books."
```php
textbin('Books'); // Echos first entry named "Books."
```

The last parameter is a boolean value to echo (true) or return (false) the item. By default itis set to true:

```php
$copyright = textbin('Copyright', true, false); // Stores the "Copyright" item in the$copyright variable.
```