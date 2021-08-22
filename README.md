# Clover-CMS

I have created this CMS for my own personal use however want to share it in case anyone else finds it helpful for their projects. As such anyone else using it may not find things very intuitive or easy to develop further. I am maintaing this repository myself and working on it in my spare time. Feel free to get in touch if you need assistance and I will do my best to help.

## Setup

Installation is as simple as downloading a copy of the repository and saving the files to the required directory.

First make sure that the .htaccess file has the correct RewriteBase location, if you are installing to a directory other than the document root. 

Then you will want to ensure that the file permissions are correct, this will most likely vary depending on your server configuration but 775/755 for directories and 664/644 for files should work best. In order for the setup process to be successful the includes directory must be writable by the server, so that a new settings.php file can be created. 

Next you will want to create a new MySQL database and go to **https://yoursite.com**. You should be redirected automatically to the setup page. On this page fill out your MySQL database details, and then an email and password for your initial admin account. After submitting you should see a message explaining that installation is complete and you can now login to your admin account. You should also receive an email confirming this.

You can click the link on the confirmation message or in the email to get to the admin login, or go to **https://yoursite.com/admin-login/**. The initial account always has the username **admin**.

###Bootstrap
You may also wish to install bootstrap via npm, Clover CMS is using bootstrap 5.0.2, 5.1.0 has caused some issues so I would avoid it for now. The only reason you should need to install bootstrap separately is to use with scss. The two existing scss files both import from **../node_modules/bootstrap/scss/**. If you want to make changes to these files then you should also install bootstrap. 

> npm install bootstrap@5.0.2

## General Usage

After logging in to the cms you will be presented with a dashboard page (Currently in development). In the left hand sidebar are several pages.

### Pages / News

Pages and News both follow a very similar layout as they are both post types. You can create custom post types by adding new entries to the **post_types** table within the database. Post types should be entered in all lowercase with hyphens instead of spaces. 

Pages will be given the url structure **https://yoursite.com/page-url**, whereas news and all other custom post types follow the structure **https://yoursite.com/post-type/post-url**

Going to Pages or News you will be presented with a list view of the content type. On the left is a create new content button and below that is a search bar. As you create content each item will appear within a table on the right side. 10 items will be displayed per page, in the order of most recently edited first. Next to each item is an edit or delete button.

Creating or editing a content item will take you to the single view for that item. Within this page are various fields to fill out. 
* Name - used for reference in the CMS and will be used as the Meta title if one isn't provided
* URL - the url for the page, this must be unique even amongst other post types
* Template - the template file used to display this item's content [See templates](## Templates)
* Author - mostly used for news or similar custom post types, the author will display on the page. Also used as meta author if one isn't provided
* Date Created - again mostly for news, this date will appear on news posts as the published date
* Visibility - hidden will not appear, draft can be accessed as long as you are logged in to the cms, visible is visible to everyone
* Meta - the various meta tags which are used for search engines
* Featured image - Select an image from the media manager, currently used for news to be displayed on the list view, but can be integrated into custom templates to be display wherever you want
* Excerpt - this is another field mostly used for news, the excerpt will appear on the list view of the news post type
* Content - a TinyMCE 5 editor for creating the content for the item

### Forms

You can create forms that can be displayed within a page by copying the displayed shortcode into the TinyMCE editor. In the form editor you can set a name for the form which is used for your reference only, with the exception of a contact form script that has already been provided. 

On the right hand side is the form structure. The Form ID will be automatically generated, you can create a custom php script and link the form to it actions are already located in **includes/actions/script.php** but you can put your scripts wherever you want. You can then select if you want the form to be POST or GET.

Click the + Group button to create a group of inputs. Each group will be displayed as a collapsable section with a header. If only one group exists within a form then the collapse functionality is ommitted. If only a single group exists and no group name is provided then the header section will also be ommitted entirely.

After creating a group and giving it a name you can expand it and start adding inputs. Select an input type from the dropdown and click the + Input button. You can expand each input you create and set various options for that input.

#### Contact Forms

By default an action has already been provided to use the form as a contact form **includes/actions/formsubmit.php** ensure you are using POST. Contact forms also work with Google Recaptcha v3 Invisible site and secret keys which can be entered in Settings. If one or both is not provided then the form will not attempt to use the captcha. The script will send emails to the email address also stored within Settings.

### Navigation

On the left side you will be able to select which navigation menu you want to amend, new menus can be created by adding new rows to the **naviagation_menus** table in the database. You will then need to display the menu somewhere, two classes are provided **new navbar($menuid)** and **new verticalnav($menuid)** or you can create your own.

Also on the left you can insert new items into the menu, either select an existing content item from the dropdown menu to autofill the fields, or enter the information yourself. After clicking insert the item will be added to the end of the structure.

You can edit each item within the structure to change the name, url and visiblity of the item. After making your changes close the pop-up window and click Save Navigation Structure. You are also able to re-order the menu by dragging and dropping the items, again saving the structure when finished.

### Users

In this section you can create new users who can log into the cms, or update existing ones. Each user's email address must be unique.

### Settings

Website details contains all of the information that you might want to display about your company or site. The name of the website, address and contact details. Note that the email address here is also used for contact forms if using the provided **includes/actions/formsubmit.php** script. These details generally appear within the footer, and possibly the header too.

Page settings lets you choose your homepage, and which page to display news posts. If no homepage is set then the first page in the posts table in the database will be used as the homepage. If no news page is set then news posts simply won't be displayed anywhere.

Social media lets you link to various social media profiles, these will appear within the footer. You can create custom profiles by adding to the socail_links table within the database. The names should be stored as lower case with hyphens instead of spaces. Font awesome is used to display the logo, so make sure that they have an icon available before creating new social media accounts, the name set in the database should match what Font Awesome uses without the fa- prefix.

### Media Manager

To handle file management Clover CMS uses [Responsive Filemanager](https://github.com/trippo/ResponsiveFilemanager). You are able to upload files, rename, delete and organsise using folders. Responsive filemanager is also used when inserting media into Tiny MCE or selecting images such as featured images and the site logo.

The latest full release 9.14.0 is not compatible with PHP8.0, so the latest version from Github is currently being used, although this isn't a full release and doesn't seem to be working perfectly. 

If you have any issues then you can change back to 9.14.0 by renaming the **js/responsive_filemanger** directories. Delete or preferably rename responsive_filemanger to something else, then rename responsive_filemanager_php7 to responsive_filemanager. This version has been tested working on PHP7.4.

## Templates

Content is displayed by default using the page.php file in the root directory. However you can create your own files within **includes/templates/** to display content however you like, look at page.php for reference. After saving your template file you can select it from the Template dropdown menu within each content item's single view.

## Shortcodes

Shortcodes are PHP functions which can be inserted into pages through Tiny MCE. Shortcodes are stored within **includes/shortcodes.php** although you can create whatever files you like as long as they are included before the page header.

Shortcodes follow the format `[function_name parameter1="value" parameter2="value2"]`

Your function should accept an array of parameters rather than each parameter separated by a comma, for example.

`function function_name($parameters = []) {
    if(isset($parameters['parameter1'])) {
        return $parameters['parameter1'];
    }
}`

Function names should consist of letters, numbers, hyphens and underscores only. This is also tru for parameter names. Parameter values can be almost anything you like that can be stored as a sting within the two double quotes. 

Note: You should be able to pass double quotes as a parameter value, but make sure you escape them with a backslash `\"`.

## Credits

* [Bootstrap](https://github.com/twbs/bootstrap)
* [Responsive Filemanger](https://github.com/trippo/ResponsiveFilemanager)
* [Tiny MCE](https://www.tiny.cloud/)
* [Font Awesome](https://fontawesome.com/)
* [Fancybox](https://fancyapps.com/docs/ui/fancybox/)
* [Retina JS](https://imulus.github.io/retinajs/)