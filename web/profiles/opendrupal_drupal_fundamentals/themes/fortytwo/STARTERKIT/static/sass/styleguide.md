# Styleguide for the STARTERKIT theme

![Logo](../static/images/fortytwo_logo.svg "Logo")

|              |                   |
|--------------|-------------------|
| Client       | Acme inc          |
| Authored by  | Webdev company    |
| Designed by  | Design agency     |
| Contact info | frontend@test.com |
|              | support@test.com  |

## About this Styleguide

This styleguide for the STARTERKIT Drupal theme is generated with `KSS`, a
stylesheet parser,
[Spec available](https://github.com/kss-node/kss/blob/spec/SPEC.md).

The STARTERKIT theme is a subtheme based on
[FortyTwo](https://www.drupal.org/project/fortytwo).
To add items to this styleguide just add comments in your SASS file and
regenerate this styleguide by running `grunt styleguide` from the static folder.

Here's an example KSS comment:

<pre class="prettyprint linenums lang-css"><code data-language="css">/*
Button

Your standard button suitable for clicking.

:hover   - Highlights when hovering.
.shiny   - Do not press this big, shiny, red button.

Markup: button.html

Style guide: components.button
*/
.button {
  ...
}
.button.shiny {
  ...
}
</code></pre>

*The contents of this file can be changed by editing the `styleguide.md` markdown
file found in the `static/sass` directory of the STARTERKIT drupal theme.*

**For more information on how to write KSS comments, see the
[KSS spec](https://github.com/kss-node/kss/blob/spec/SPEC.md).**


---
