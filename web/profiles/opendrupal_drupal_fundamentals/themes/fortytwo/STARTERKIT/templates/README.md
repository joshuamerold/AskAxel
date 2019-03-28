#TEMPLATES
----------
Drupal 8 contains the following template files which you can override and modify
by copying them to your sub-theme.

The Forty Two theme overrides a handful of Drupal's templates. In order to override
those templates, you should copy them from the fortytwo/templates folder to your
sub-theme's templates folder.

As always, when adding a new template file to your sub-theme, you will need to
rebuild the "theme registry" in order for Drupal to see it. For more info, see:
[https://drupal.org/node/173880#theme-registry]

The following files can be overridden by copying them to you subfolder from fortytwo/templates:

- block:  
  - block--search-form-block.html.twig  
  - block.html.twig  
- content:  
	- comment.html.twig  
	- node.html.twig  
- dataset:  
	- item-list.html.twig
- field:
	- field.html.twig
- form:
	- details.html.twig
	- form-element-label.html.twig
	- form-element.html.twig
- layout:
	- html.html.twig
	- maintenance-page.html.twig
	- page.html.twig
	- region.html.twig
- misc:
	- status-messages.html.twig
- navigation:
	- breadcrumb.html.twig
- views:
	- views-view-list.html.twig
	- views-view-unformatted.html.twig
	- views-view.html.twig
