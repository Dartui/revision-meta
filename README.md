# Revision Meta
WordPress plugin for storing meta with post revisions

# Usage
- register post type with `revision_meta` key and `revision` support or use `revision_meta_fields` filter for already registered post types

	```php
	add_action('init', 'register_custom_post_type');

	function register_custom_post_type() {
		$args = array(
			'label'         => __('Post Type', 'text-domain'),
			...
			'supports'      => array('revisions', ...),
			'revision_meta' => array('first_meta_key', 'second_meta_key', ...),
		);

		register_post_type('post-type', $args);
	}

	OR

	add_filter('revision_meta_fields', 'add_revision_meta_fields', 10, 3);

	function revision_meta_fields($meta_fields, $post_type, $post_type_object) {
		if ($post_type == 'post-type') {
			$meta_fields = array(
				'first_meta_key',
				'second_meta_key',
			);
		}

		return $meta_fields;
	}
	```
- create metabox for post type with fields which names match the meta keys

	```html
	<input type="text" name="first_meta_key" />
	<select name="second_meta_key">
		<option value="1">One</option>
		<option value="2">Two</option>
		...
	</select>
	```