<?php
/* Plugin Name: Revision Meta
 * Plugin Description: Plugin for storing meta with post revisions
 * Version: 1.0.1
 * Author: Krzysztof Grabania
 * Author URI: http://grabania.pl/
 */

if (!defined('ABSPATH')) {
	exit;
}

class RevisionMeta {
	public function __construct() {
		add_filter('wp_save_post_revision_post_has_changed', array($this, 'save_post_revision'), 10, 3);
		add_action('_wp_put_post_revision', array($this, 'put_post_revision'), 10, 1);
		add_action('wp_restore_post_revision', array($this, 'restore_post_revision'), 10, 2);
		add_action('wp_delete_post_revision', array($this, 'delete_post_revision'), 10, 2);
	}

	public function save_post_revision($post_is_changed, $last_revision, $post) {
		if (!$revision_meta = $this->get_revision_meta_fields($post->ID)) {
			return $post_is_changed;
		}

		$old_meta = array();
		$new_meta = array();
		foreach ($revision_meta as $meta_key) {
			$last_revision_value = get_post_meta($last_revision->ID, $meta_key, true);
			$post_revision_value = filter_input(INPUT_POST, $meta_key);
			
			$new_meta[$meta_key] = $post_revision_value ? $post_revision_value : '';
			$old_meta[$meta_key] = $last_revision_value ? $last_revision_value : '';
		}

		return $this->meta_has_changed($old_meta, $new_meta) || $post_is_changed;
	}

	// save meta to revision
	public function put_post_revision($revision_id) {
		if ($revision_meta = $this->get_revision_meta_fields($revision_id)) {
			foreach ($revision_meta as $meta_key) {
				$meta_value = filter_input(INPUT_POST, $meta_key);
				
				$this->update_or_delete($revision_id, $meta_key, $meta_value);
			}
		}
	}
	
	private function update_or_delete($revision_id, $meta_key, $meta_value) {
		if ($meta_value) {
			return update_metadata('post', $revision_id, $meta_key, $meta_value);
		}
		
		return delete_metadata('post', $revision_id, $meta_key);
	}

	public function restore_post_revision($post_id, $revision_id) {
		if ($revision_meta = $this->get_revision_meta_fields($revision_id)) {
			foreach ($revision_meta as $meta_key) {
				if ($revision = get_post_meta($revision_id, $meta_key, true)) {
					update_post_meta($post_id, $meta_key, $revision);
				}
			}
		}
	}
	
	public function delete_post_revision($revision_id) {
		if ($revision_meta = $this->get_revision_meta_fields($revision_id)) {
			foreach ($revision_meta as $meta_key) {
				delete_metadata('post', $revision_id, $meta_key);
			}
		}
	}

	private function get_post_type($post_id) {
		$post = get_post($post_id);

		if ('revision' == $post->post_type) {
			$post = get_post($post->parent);
		}

		$post_type_name = get_post_type($post->ID);

		return get_post_type_object($post_type_name);
	}

	private function get_revision_meta_fields($the_object) {
		$object = $this->get_object($the_object);
		
		if (!$object) {
			return false;
		}
		
		$meta_fields = array();

		if (!empty($object->revision_meta) && is_array($object->revision_meta)) {
			$meta_fields = $object->revision_meta;
		}

		$meta_fields = array_filter(apply_filters('revision_meta_fields', $meta_fields, $object->post_type, $object));

		if (empty($meta_fields)) {
			return false;
		}

		return $meta_fields;
	}
	
	private function get_object($the_object) {
		if (!$the_object) {
			return false;
		}
		
		if (is_numeric($the_object)) {
			return $this->get_post_type($the_object);
		}
		
		return $the_object;
	}

	private function meta_has_changed($old = array(), $new = array()) {
		$old_json = json_encode($old);
		$new_json = json_encode($new);
		similar_text($old_json, $new_json, $percent);

		return 100 != $percent;
	}
}

new RevisionMeta();