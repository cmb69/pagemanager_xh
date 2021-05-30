# Pagemanager\_XH

Pagemanager\_XH simplifies the administration of pages of a CMSimple\_XH
installation. This plugin is comparable to the Menumanager plugin with
regard to functionality, but by using a treeview it allows for better
handling of websites with many pages. With Pagemanager\_XH it is also
possible to rearrange whole submenus.

## Table of Contents

  - [Requirements](#requirements)
  - [Download](#download)
  - [Installation](#installation)
  - [Settings](#settings)
  - [Usage](#usage)
  - [Limitations](#limitations)
  - [Troubleshooting](#troubleshooting)
  - [License](#license)
  - [Credits](#credits)

## Requirements

Pagemanager\_XH is a plugin for CMSimple\_XH ≥ 1.7.0. Additionally it
requires the jQuery4CMSimple and Fa\_XH plugins, which are already
contained in the standard download of CMSimple\_XH. It also requires PHP
≥ 5.3.0 with the JSON extension.

## Download

The [lastest release](https://github.com/cmb69/pagemanager_xh/releases/latest) is available for download on Github.

## Installation

The installation is done as with many other CMSimple\_XH plugins. See
the [CMSimple\_XH
wiki](https://wiki.cmsimple-xh.org/doku.php/installation) for further
details.

1.  Backup the data on your server.
2.  Unzip the distribution on your computer.
3.  Upload the whole directory pagemanager/ to your server into
    CMSimple\_XH's plugins/ directory.
4.  Set write permissions to the subdirectories css/, config/ und
    languages/.
5.  Browse to Pagemanager's administration (*Plugins* → *Pagemanager*),
    and check if all requirements are fulfilled.

## Settings

The plugin's configuration is done as with many other CMSimple\_XH
plugins in the website's back-end. Select *Plugins* → *Pagemanager*.

You can change the default settings of Pagemanager\_XH under *Config*.
Hints for the options will be displayed when hovering over the help icon
with your mouse.

Localization is done under *Language*. You can translate the character
strings to your own language if there is no appropriate language file
available, or customize them according to your needs.

The look of Pagemanager\_XH can be customized under *Stylesheet*.

## Usage

This plugin is used exclusively in CMSimple\_XH's backend. It is started
by clicking *Pages* in the administration menu. Now you see a view of
your site's structure, that basically resembles the sitemap. However, it
is possible to open and close page branches in this view, so you don't
loose overview of even extensive websites. You can open or close those
by clicking on the small markers left to the page, or by double clicking
their titles.

Above the tree view of your website's pages you find Pagemanager\_XH's
**toolbar**. The toolbar is particularly useful, if your browser doesn't
allow Pagemanager\_XH's context menu to popup. Note that you can disable
the toolbar in the plugin's configuration.

Rearrangement of the structure of your pages is done by **drag & drop**;
when dragging according markers are shown, so you can see where the page
will be inserted when dropping it. If it is not possible to drag to a
certain page, because it would result in e.g. a recursive page
structure, or the resulting nesting level would be too high, this will
be signalled by a cross or you will simply be not allowed to drop there.
*So carefully watch the markers,* until you get a feeling how the drag &
drop works. If you hold down the CTRL key while dragging, the result
will be a copy instead of a move operation.

Additional functionality is available in the toolbar or through the
**context menu** (click the right mouse button on the page). You can add
new pages, rename or remove existing ones (*if you delete the selected
page, all it's subpages will be deleted as well.*), use the common
clipboard functions as an alternative to drag & drop, and navigate
directly to a page, either in *edit* or *view* mode. Note that
functionality that is currently unavailable is disabled. For instance,
most of the functionality requires that a page is selected; if no page
is selected, these functions are disabled (greyed out). Another example
is the *paste* → *inside* function, which is not available if the page
in the clipboard should be pasted into itself.

The checkboxes to the left of the pages allow you to view and change
their **publishing state**. You can configure if they refer to
'Published?' or 'Show in menu?' (what is the default).
If this setting is empty, no checkboxes are shown.

**Duplicate headings** are marked by a warning icon. It's best to fix
these right away. **Newly created pages** will be shown with a filled
folder icon until the next *save*, to better distinguish them from old
pages. **Pages that can't be renamed** because their headings contain
additional markup are marked with a *tag* icon; it is okay to have such
pages, but if you want to remove the additional markup you have to do
this in the editor.

**Soft-hyphens** (`&shy;`) in the page headings are displayed as `|-|`;
additinal soft-hyphens can be inserted by entering this character sequence.
On saving these are converted to proper soft-hyphens again.

The possiblitly to copy whole substructures might not seem resonable at
first sight, but it could be useful, e.g. if you've got a gallery on
those pages, because all content *and* meta data of these pages will be
copied too. So it is possible to adjust details afterwards.

Note that there is no undo or cancel functionality. If you've totally
mixed up your page structure simply refresh your browser's view
*without* saving before. Your old page structure will be presented
again.

## Limitations

### Irregular page structures

It is possible that your existing website has an irregular page
structure. E.g. after an `<h1>` heading immediately follows an `<h3>`
heading without an `<h2>` heading in between. Such irregularities in
your page structure might have unintentionally been introduced while you
were manually editing the page structure in the editor (e.g. changing
headings, deleting pages), but it is possible that this feature is used
by your system for a special purpose.

Anyhow, Pagemanager can't handle such irregular page structures, and if
it detects it, it shows a message and offers you to fix it. If the
irregularity was introduced accidentially, you may safely confirm and go
on. Otherwise (or if you're not sure) take a backup of the contents file
before you proceed, save the fixed structure from Pagemanager, and
carefully check, if everything still works as expected.

### jQuery

Pagemanager\_XH *may* not work in installations with jQuery dependent
plugins/addons/templates that don't use jQuery4CMSimple, but import
their own jQuery library. This won't get fixed (as it's not possible to
fix it in all cases), because all developers are advised to use only
jQuery4CMSimple together with all their jQuery based code for
CMSimple\_XH. So offending extensions should be updated\!

### Drag & Drop in Internet Explorer

There are known issues regarding drag & drop operations in Internet
Explorer. Particularly, moving or copying a page inside another page
doesn't work with the default admin menu. The cause is a [limitation in
jQuery](https://github.com/jquery/jquery/issues/3676). Either use the
"clipboard" functionality, upgrade to Edge (or another browser), or use
an alternative admin menu.

## Troubleshooting
Report bugs and ask for support either on [Github](https://github.com/cmb69/pagemanager_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Pagemanager\_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pagemanager\_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pagemanager\_XH.  If not, see <http://www.gnu.org/licenses/>.

© 2011-2021 Christoph M. Becker

Czech translation © 2011-2014 Josef Němec  
Danish translation © 2011-2014 Jens Maegaard  
Dutch translation © 2014 Emile Bastings  
Estonian translation © 2014 Alo Tanavots  
French translation © 2011-2014 Patrick Varlet  
Italian translation © 2014 Milko Dalla Battista  
Slovak translation © 2011-2014 Dr. Martin Sereday

## Credits

This plugin uses [jsTree](http://www.jstree.com/). Many thanks to Ivan
Bozhanov, the developer of this library. jsTree uses
[jQuery](http://jQuery.com). Many thanks to all developers of this
JavaScript framework. jQuery is made available for CMSimple\_XH by
[jQuery4CMSimple](http://www.cmsimple-xh.org/wiki/doku.php/extend:jquery4cmsimple).
Many thanks to Holger Irmler, the author of this plugin.

The *proton* theme for jsTree is a slightly modified version of [jsTree
Bootstrap Theme](https://github.com/orangehill/jstree-bootstrap-theme).
Thanks for publishing this nice theme under MIT license.

This plugin uses [Font Awesome by Dave Gandy](http://fontawesome.io/).
Many thanks for making this great iconic font and CSS toolkit available
under a GPL friendly license.

The plugin icon is designed by [Everaldo
Coelho](http://www.everaldo.com/). Many thanks for publishing this icon
under GPL.

Many thanks to the community at the [CMSimple\_XH
forum](http://www.cmsimpleforum.com/) for tips, suggestions and testing.
Particularly I want to thank *snafu*, who's early feedback encouraged me
to go on with Pagemanager\_XH. Many thanks to *Ulrich*, who found a
severe bug (and some minor issues) and helped to fix it by providing
detailed information on what's happened. And many thanks to *Gert* for
some bugfixes and translations and many valuable hints. I also want to
thank *Martin*, whose report about problems with the context menu
inspired the addition of the toolbar, and Tata who inspired the
"scrolling" toolbar.

And last but not least many thanks to [Peter Harteg](http://harteg.dk/),
the "father" of CMSimple, and all developers of
[CMSimple\_XH](http://www.cmsimple-xh.org/) without whom this amazing
CMS wouldn't exist.
