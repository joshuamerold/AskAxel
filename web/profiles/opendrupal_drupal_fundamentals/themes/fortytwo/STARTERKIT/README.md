# BUILDING A THEME WITH FORTY TWO

The FortyTwo theme is designed to be extended by a sub-theme. You shouldn't modify the
any of the CSS or PHP files in the fortytwo/ folder; instead create a
sub-theme which is located out side of the root fortytwo/ folder.

We tried to make the base theme as clean and simple as possible. It has some
styling for basic input fields and buttons.

## FortyTwo base theme location

FortyTwo follows the specifications of theme locations as described on the page
https://www.drupal.org/docs/8/theming-drupal-8/drupal-8-theme-folder-structure.

So it is important to install the FortyTwo base theme in the folder `/themes/contrib/fortytwo`

## Setup the location for your new sub-theme.  

  Copy the STARTERKIT folder out of the fortytwo/ folder and rename it to be your new sub-theme.  
  **IMPORTANT:** The name of your sub-theme must start with an *alphabetic character* and can only
  contain *lowercase letters, numbers and underscores*.

  For example, copy the `/themes/contrib/fortytwo/STARTERKIT` folder and rename it as `/themes/custom/foo`.

  Why? Each theme should reside in its own folder. To make it easier to upgrade FortyTwo, sub-themes should
  reside in a folder separate from the base theme.

## Automated setup of base theme with drush

  You can use drush to setup your new base theme. Follow the steps below and
  consult `drush help fortytwo` for help.

  1. **Ensure drush knows about the fortytwo command**

    After you have downloaded FortyTwo and placed it in your `themes`
    directory, you need to enable the FortyTwo theme and set FortyTwo as the
    default theme on the Appearance administrative page.

    Type: `drush en fortytwo` and go to Administrative Menu > Appearance and
    next to Fortytwo, click on *Set as default*. You can also use drush for this
    by using `drush config-set system.theme default fortytwo`.

    The `drush fortytwo` command will now be available to run.

  2. **Run the drush fortytwo command with the following parameters.**

     In the command line, use the `drush fortytwo "My theme name" my_theme`
     command to generate a subtheme with machine name foo and human name
     "Foo theme" in your Drupal site.

     Tip: Type `drush help fortytwo` to view options and example commands.


## Setup the basic information for your sub-theme.

  In your new sub-theme folder, rename the `STARTERKIT.info.yml.txt` file to include
  the name of your new sub-theme and remove the ".txt" extension. Then edit
  the .info.yml file by editing the name and description field.

  For example, rename the `foo/STARTERKIT.info.yml` file to `foo/foo.info.yml`. Edit the
  foo.info.yml file and change `"name = Forty Two Sub-theme Starter Kit"`. Do the same
  with the description property.
  Make sure you also rename the libraries section `STARTERKIT/main` to `foo/main` in
  the .info.yml file.

  Also rename the STARTERKIT.libraries.yml and the STARTERKIT.theme files. For example rename
  the `foo/STARTERKIT.libraries.yml` file to `foo/foo.libraries.yml` and the `foo/STARTERKIT.theme`
  file to `foo/foo.theme`. Replace any occurences of STARTERKIT in any of these files.

  The main javascript file needs to be renamed in order to work. A reference to
  that file can be found in the subtheme's `.info.yml` file, and the file itself is
  located in the folder `static/js`. Rename the file to whatever you want and
  change the reference to that file in the `.info.yml` file. Best practice is to
  name it like the theme's machine name. Also rename the behavior in the
  `STARTERKIT.js` file to reflect your theme's name.

  Do not forget to rename the file in the config folder as well.

## Configuration of your subtheme.

  A lot of settings can be done on the sub-theme appearance page. CSS/SASS specific
  settings can be set in `/static/sass/_settings.sass`.

## Responsive

  By default Forty Two theme has 5 separate layouts;
  desktop, tablet-landscape, tablet-portrait, mobile-landscape, mobile-portrait.
  The media queries are defined in the `/static/sass/_settings.sass` file.

  You can enable the responsive layout on the `admin/appearance` page. There are two
  types of responsive behaviour you can select:  
  *adaptive* and *fluid*.

  * Adaptive means that the layout will 'snap' to the next when media queries match. Everything
    is calculated in pixels.

  * Fluid means that everything is calculated in percentages, every element will be kept in
    the same spatial weighting, no 'snapping' will occur.

  The grid-system used is loosly based on the 978 grid-system (http://978.gs/). The
  grid-configuration for each layout can be modified in the corresponding sass files
  which are located in the main theme grid folder: `/fortytwo/static/sass/theme/base/grid/`.
  When you copy this file to your subtheme and include them in the `main.sass` you are
  able to edit them.

  At the top of each `_[..]` sass file you can define the number of columns and the
  column and gutter sizes. Only the desktop version (`_desktop.sass`) inherits its
  column configuration from the `/static/sass/_settings.sass` file. You can change it there.

### IMPORTANT NOTE  
  Giving elements the proper width based on column numbers should be done in these files.

  There are two ways for column-based calculations, with the mixin `span-columns` or with
  the function `calc-grid`. The span-columns mixin will satisfy most of the time. When using
  the fluid grid you need to use `span-fluid-columns` or `calc-fluid-grid`.
  More info on how these mixins/functions work you can check the comments in the
  `/static/sass/lib/_mixins.sass` file.

## Javascript

  There are some libraries and polyfills provided that can be turned on/off.

## Gulp

  You can use Gulp in this starter kit. Just do `npm install` in the theme's folder
  for installing and use `gulp` for running the watcher. We've added some nice features:
  * Sass compiling, including source maps and autoprefixer;
  * JS Hint, and uglify;
  * Cache clear when you change php, inc or info files in the theme;
  * Livereload is added to the gulp file. To make this work you need the browser plugin 
    which you can find on http://livereload.com/extensions/. Also you need to enable it by changing the `enable_livereload` value in the `gulp.config.json` file to true. With the option `livereload_hard_refresh` you can enable or disable window refreshes. When this option is set to false (default) only the static files will be reloaded.
