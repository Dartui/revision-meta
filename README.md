# Revision Meta
Plugin for storing meta for post revisions

# Usage
- register post type with `revision_meta` key and `revision` support

	```php
	$args = array(
		'label'         => __('Post Type', 'text-domain'),
		...
		'supports'      => array('revisions', ...),
		'revision_meta' => array('first_meta_key', 'second_meta_key', ...),
	);

	register_post_type('post-type', $args);
	```
- create metabox for post type with fields which names that match the meta keys

	```html
	<input type="text" name="first_meta_key" />
	<select name="second_meta_key">
		<option value="1">One</option>
		<option value="2">Two</option>
		...
	</select>
	```