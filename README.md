Cheezy
======

This framework can indeed has cheezburger.

Think of it as [Zynapse][]'s little brother:

* No ActiveRecord
* One Single Framework Provided Controller
* Easy
* Peasy
* Rice


Basic Usage
-----------

Easiest thing to do is to take this example project, and start making changes.

### Layouts

Your main layout should be named `default.php` inside `_layouts/`. To change layouts just make a new php file 
(for example: `new_layout.php`) in `_layouts`. To use it on a page, add `<?php $this->layout = 'new_layout'; ?>` 
somewhere on the page you want to have use your new layout.

You'll need to add `<?php echo $content_for_layout; ?>` where ever you want the layout to include the page content.


### Pages

The default page the framework will try to render is `home.php` inside `_pages`. To add a new page to your site, 
say something like `http://example.dev/bio/` just make a new file called `bio.php` and put it inside `_pages`.

### Partials

To make a partial, make a new file in `_partials` (for example: `header.php`). To include that partial in your page
or layout, add `<?php render_partial('header'); ?>` wherever you want it to be included.

### Error Pages

You can customize the error pages by making a new php file called `default.php` in `_errors`. Alternatively, you
can serve a specialized page per HTTP error code by putting a file named after the error code number into `_errors`
(for example: `404.php`). Check out the `framework/_errors/default.php` to see the variables you can use in
your error pages.


### Post "Installation"

You'll need to give read/write permissions (`chmod 777`) to the `tmp` directory. This allows the framework to do
a little cacheing to speed up processing time.


[Zynapse]: http://github.com/jimeh/zynapse/