=== Coyote ===
Contributors: jkva
Requires at least: 6.0.0
Tested up to: 6.2
Requires PHP: 8.0
Stable tag: 2.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Coyote WordPress plugin allows for images in posts and pages to have their text description ("alt attribute") be managed by a Coyote (https://www.coyote.pics) service.

== Description ==

When the plugin is installed and its filters are enabled, it replaces image alt attributes in your posts with those retrieved from a Coyote service.
When Coyote service moderators change image descriptions and remote updates are enabled, those filters will also reflect these updates.

== Read more ==

See `readme.MD` for more information on various settings and tools.

== Use of 3rd party service ==

Once the plugin is set up to connect to a valid [Coyote service](https://www.coyote.pics) API account, image meta-data stored in your WordPress installation may potentially be stored by the Coyote service.
The plugin may also query the Coyote service to provide necessary functionality.
Please familiarise yourself with the [Coyote Terms of Service](https://www.coyote.pics/terms-of-service/).

== Changelog ==

=2.6=
* Downgrade minimal required PHP to 8.0

=2.5=
* Update minimal required PHP to 8.1, WP to 6.0
* Remove monolog dependency
* Add explicit debugging setting
* Upgrade coyote-api-client version

=2.4=
* Allow batch processing without needing a secondary control server
* Fix caption whitespace bug

=2.3=
* Upgrade coyote-api-client version

=2.2=
* Ignore whitespace image captions
* Filter organization memberships by role

=2.1=
* Scope dependencies via php-scoper
* Fix content parsing bug

=2.0=
* Significant rewrite
* Support for custom post types
* Split up sidebar menu

=1.7=
Check for required functions when interfacing with classic editor

=1.6=
* Hook into the alt text management field in attachment editor
* Fix - retrieve parent post by correct property

=1.5=
* Increase number of screens in which to hook the media manager

=1.4=
* Add resource group verification control on settings page

=1.3=
* Allow configurable importing of images in unpublished posts

=1.2=
* Track version and stable tag properly

=1.1=
* Fix unexpected output error on activation
* Amend markdown readme
* Implement permission_callback callback for rest api /status route
* Simplify settings language

=1.0=
* First release into the registry.
