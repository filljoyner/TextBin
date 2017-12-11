<?php
class TextBin {
	var $class_name = 'TextBin';
	var $textbin_db_version = "1.0";
	var $table_name = "textbin";
	
	var $plugin_url = null;
	var $plugin_dir = null;
	var $img_url = null;
	var $view_dir = null;
	
	var $msg = false;
	
	
	/*
	-------------------------------------------------------------------------
	STARTUP
	-------------------------------------------------------------------------
	*/
	/*
	 * Preps variables in the class.
	 */
	function TextBin() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . $this->table_name;
		
		//$this->plugin_url = plugin_dir_url(__FILE__);
		//$this->plugin_dir = plugin_dir_path(__FILE__);
		
		//$this->plugin_index = admin_url() . 'admin.php?page=textbin';
		//$this->plugin_add = admin_url() . 'admin.php?page=textbin-add';
		//$this->plugin_edit = admin_url() . 'admin.php?page=textbin&tbpage=edit';
		//$this->plugin_del = admin_url() . 'admin.php?page=textbin&tbpage=delete';
		
		//$this->img_url = $this->plugin_url . 'img/';
		//$this->view_dir = $htis->plugin_dir = 'views/';
	}
	
	
	
	/*
	 * On Activate, checks if table is current and installed. If not, installs or updates.
	 */
	function init() {
		global $wpdb;
		$current_db_version = get_option("textbin_db_version");
		if($wpdb->get_var(("SHOW TABLES LIKE '$this->table_name'") != $this->table_name) || ($this->textbin_db_version != $current_db_version)) {
			$this->install();
		}
	}
	
	
	/*
	 * Adds TextBin table to the database.
	 */
	function install() {
		global $wpdb;
		$version = get_option("textbin_db_version");
		if($version) update_option("textbin_db_version", $this->textbin_db_version);
		else add_option("textbin_db_version", $this->textbin_db_version);

		$sql = "CREATE TABLE " . $this->table_name . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			name TINYTEXT NOT NULL,
			val TEXT NOT NULL,
			filter BOOL DEFAULT '0',
			ord INT UNSIGNED DEFAULT '0' NOT NULL,
			blog_id INT UNSIGNED NOT NULL,
			UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	/*
	-------------------------------------------------------------------------
	END STARTUP
	-------------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------------
	CRUD
	-------------------------------------------------------------------------
	*/
	/*
	 * Gets all TextBin items and returns them.
	 * 
	 * @return array					Returns all TextBin items
	 */
	function readAll() {
		global $wpdb;
		$read = "SELECT * FROM {$this->table_name} ORDER BY ord ASC";
		$result = $wpdb->get_results($read);
		return $result;
	}
	
	
	/*
	 * Finds single or multiple TextBin items and returns them formatted or unformatted.
	 * 
	 * @param string $title				Title of TextBin Item to return
	 * @param BOOL $single				If returns should be a single or multiple values
	 * @return string					Returns the results
	 */
	function find($slug=null, $single=true) {
		global $wpdb;
		if(!$title) return false;
		$slug = $wpdb->escape($slug);
		$read = "SELECT * FROM {$this->table_name} WHERE name='$slug' ORDER BY ord ASC";
		
		$results = $wpdb->get_results($read);
		if($results) {
			if($single) {
				$result = ($results[0]->filter ? $this->format(do_shortcode($results[0]->val)) : do_shortcode(stripslashes($results[0]->val)));
				return $result;
			} else {
				$return = array();
				foreach($results as $result) {
					$result = ($results->filter ? $this->format(do_shortcode($result->val)) : do_shortcode(stripslashes($result->val)));
					$return[] = $result;
				}
				return $return;
			}
		} else {
			return false;
		}
	}
	
	
	
	/*
	 * Saves a new TextBin item to the database after validation.
	 * 
	 * @param array $save				Array of Post values to be saved
	 * @return BOOL						Returns true if item has been saved
	 */
	function create($save=null) {
		$vars = $this->validate($save);
		print_r($vars);
		if(!$vars['name']) {
			return false;
		}

		global $wpdb;
		
		if($wpdb->insert($this->table_name, $vars)) {
			return true;
		}
		return false;
	}
	/*
	-------------------------------------------------------------------------
	CRUD
	-------------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------------
	HELPERS
	-------------------------------------------------------------------------
	*/
	/*
	 * Validates form input before saving to the database.
	 * 
	 * @params array $post					Post array to validate
	 * @return array						Return array of validated data
	 */
	function validate($post=null) {
		if(!$post) return false;
		global $wpdb;
		global $blog_id;
		
		print_r($post);

		$vars = array(
			'id' => null,
			'name' => null,
			'val' => null,
			'filter' => null,
			'ord' => 0,
			'blog_id' => $blog_id
		);
		
		$_post = array_merge(array(
			'textbinId' => null,
			'textbinName' => null,
			'textbinVal' => null,
		), $post);
		
		if($_post['textbinId'] && is_numeric($_post['textbinId'])) $vars['id'] = $_post['textbinId']; 
		if($_post['textbinName']) $vars['name'] = $_post['textbinName'];
		if($_post['textbinVal']) $vars['val'] = $_post['textbinVal'];
		if($_post['textbinFilter'] == 'filter') $vars['filter'] = true; else $vars['filter'] = false;
		
		return $vars;
	}
	
	
	/*
	 * Clears vars so there are no returns.
	 */
	function clearVars() {
		$this->vars = array();
	}
	
	
	/*
	 * Formats textbin items if required
	 * 
	 * @param string $val					String to format
	 * @param BOOL $full					True or false if the string should be fully formatted
	 * @param BOOL $string					True or false if the string should have the slashes stripped
	 */
	function format($val=null, $full=true, $strip=true) {
		if($val) {
			if($strip) $val = stripslashes_deep($val);
			if($full) {
				return apply_filters('the_content', $val);
			} else {
				return $val;
			}
		}
	}
	/*
	-------------------------------------------------------------------------
	END HELPERS
	-------------------------------------------------------------------------
	*/
}