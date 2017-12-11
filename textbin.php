<?php
/*
Plugin Name: TextBin
Plugin URI: http://wordpressfire.com/plugin/textbin/
Description: Stores text data. Used for copyright dates, addresses, phone numbers, promos, etc. Any where you'd need to store text.
Version: 2.0
Author: Philip Joyner
Author URI: http://wordpressfire.com/
Last Updated: 2013-03-04
*/


class TextBin {
	var $class_name = 'TextBin';
	var $textbin_db_version = "1.0";
	var $table_name = "textbin";
	
	var $plugin_url = null;
	var $plugin_dir = null;
	var $img_url = null;
	var $view_dir = null;
	
	var $sep_field = '<TBFIELD-----!!!>';
	var $sep_row = '<TBROW-----!!!>';
	
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
		
		$this->plugin_url = plugin_dir_url(__FILE__);
		$this->plugin_dir = plugin_dir_path(__FILE__);
		
		$this->plugin_index = admin_url() . 'admin.php?page=textbin';
		$this->plugin_add = admin_url() . 'admin.php?page=textbin-add';
		$this->plugin_edit = admin_url() . 'admin.php?page=textbin&tbpage=edit';
		$this->plugin_del = admin_url() . 'admin.php?page=textbin&tbpage=delete';
		
		$this->img_url = $this->plugin_url . 'img/';
		$this->view_dir = $this->plugin_dir = 'views/';
		
		$this->view = 'index';
		if(isset($_GET['tbpage'])) {
			if($_GET['tbpage'] == 'edit') $this->view = 'edit';
			if($_GET['tbpage'] == 'delete') $this->view = 'delete';
		}
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
		
		$update['blog_id'] = 1;
		$wpdb->update($this->table_name, $update, array('blog_id' => '0'));
		
		
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
		global $blog_id;
		$read = "SELECT * FROM {$this->table_name} WHERE blog_id=$blog_id ORDER BY ord ASC";
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
		global $blog_id;
		if(!$slug) return false;
		
		$_slug = sanitize_title_with_dashes($slug);
		$read = "SELECT * FROM {$this->table_name} WHERE name='$_slug' AND blog_id=$blog_id ORDER BY ord ASC";
		$results = $wpdb->get_results($read);		
		
		if(!$results) {
			$slug = $wpdb->escape($slug);
			$read = "SELECT * FROM {$this->table_name} WHERE name='$slug' AND blog_id=$blog_id ORDER BY ord ASC";
			$results = $wpdb->get_results($read);
		}
		
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
	 * Reads a single TextBin
	 * 
	 * @param numeric $id				Returns TextBin item with id of $id
	 * @param string $fields			The fields to return as a SQL request
	 * @return array					Returns results array if exists
	 */
	function read($id=null, $fields='*') {
		global $wpdb;
		global $blog_id;
		if(!is_numeric($id)) return false;
		$read = "SELECT $fields FROM {$this->table_name} WHERE id=$id AND blog_id=$blog_id";
		$result = $wpdb->get_results($read);
		if($result) return $result[0];
	}
	
	
	
	/*
	 * Updates an item in TextBin
	 * 
	 * @param array $save				Post array to save
	 * @return BOOL						returns true or false if item was updated
	 */
	function update($save=null) {
		$vars = $this->validate($save);
		if(empty($vars['id']) || empty($vars['name'])) {
			return false;
		}
		global $wpdb;
		if($wpdb->update($this->table_name, $vars, array('id' => $vars['id']))) {
			return true;
		}
		return false;
	}
	
	
	/*
	 * Deletes and item from TextBin
	 * 
	 * @param numeric $id					id of TextBin item to delete
	 * @return BOOL							Returns true or false if item was deleted
	 */
	function del($id=null) {
		global $wpdb;
		if(!is_numeric($id)) return false;
		$delete = "DELETE FROM {$this->table_name} WHERE id=$id LIMIT 1";
		$result = $wpdb->query($delete);
		if($result) {
			return true;
		}
		return false;
	}
	
	
	/*
	 * Returns search results based on a given term
	 * 
	 * @param string $term					string to search textbin names and values
	 */
	function search($term=null) {
		global $wpdb;
		global $blog_id;
		$search = "
			SELECT * FROM {$this->table_name} 
			WHERE 
				(
					name LIKE '$term' 
					OR name LIKE '$term%' 
					OR name LIKE '% $term' 
					OR name LIKE '% $term%' 
					OR val LIKE '$term' 
					OR val LIKE '$term%' 
					OR val LIKE '% $term' 
					OR val LIKE'% $term%'
				)
				AND blog_id=$blog_id
		";
		return $wpdb->get_results($search);
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
		if($_post['textbinName']) $vars['name'] = sanitize_title_with_dashes($_post['textbinName']);
		if($_post['textbinVal']) $vars['val'] = $_post['textbinVal'];
		if($_post['textbinFilter'] == 'filter') $vars['filter'] = true; else $vars['filter'] = false;
		if($_post['textbinOrd'] && is_numeric($_post['textbinOrd'])) $vars['ord'] = $_post['textbinOrd']; else $vars['ord'] = 0;
		
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
	 * Save Drag and Drop reordering to the Database
	 */
	function reorder() {
		global $wpdb;
		parse_str($_REQUEST['sort'], $sort);
		if(empty($sort['textbin'])) return false;
		foreach($sort['textbin'] as $ord => $id) {
			$item = $this->read($id, 'ID');
			if($item) {
				$update = "UPDATE {$this->table_name} SET ord=$ord WHERE id={$item->ID}";
				$wpdb->query($update);
			}
		}
		die();
	}
	/*
	-------------------------------------------------------------------------
	END HELPERS
	-------------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------------
	ADMIN SETUP
	-------------------------------------------------------------------------
	*/
	/*
	 * Adds the TextBin tab and submenus to the WordPress admin
	 * and adds css/js on TextBin pages
	 */
	function admin_setup() {
		global $textbin_page, $textbin_subpage, $textbin_port;
		add_menu_page('View All', 'TextBin', 'manage_options', 'textbin', array(&$this, 'admin'), $this->img_url . 'bin16.png');
		$textbin_page = add_submenu_page('textbin', 'View All TextBin', 'View All', 'manage_options', 'textbin', array(&$this, 'admin'));
		$textbin_subpage = add_submenu_page('textbin', 'Add New TextBin', 'Add New', 'manage_options', 'textbin-add', array(&$this, 'admin_add'));
		$textbin_port = add_submenu_page('textbin', 'Import/Export TextBin', 'Import/Export', 'manage_options', 'textbin-port', array(&$this, 'admin_port'));
		
		add_action('admin_print_scripts-' . $textbin_page, array(&$this, 'admin_scripts'));
		add_action('admin_print_scripts-' . $textbin_subpage, array(&$this, 'admin_scripts'));
		add_action('admin_print_scripts-' . $textbin_port, array(&$this, 'admin_scripts'));
		
		add_action('load-'.$textbin_page, array(&$this, 'admin_help_tab'));
		add_action('load-'.$textbin_subpage, array(&$this, 'admin_help_tab'));
		add_action('load-'.$textbin_port, array(&$this, 'admin_help_tab'));
	}
	
	
	/*
	 * adds css/js to the head of admin pages
	 */
	function admin_scripts() {
		wp_enqueue_style('textbin_css', plugins_url('/textbin.css', __FILE__));
		wp_enqueue_script('textbin_js', plugins_url('/js/textbin.js', __FILE__), array('jquery', 'jquery-ui-sortable'));
	}
	
	
	/*
	 * Adds help tab to top right of TextBin screens
	 */
	function admin_help_tab() {
		$screen = get_current_screen();
		$screen->add_help_tab(array(
			'id' => 'textbin',
			'title' => 'TextBin',
			'content' => 	'<p>
								<strong>The List</strong><br />
								This screen shows all your TextBin items. Sort them by drag and drop using the arrow beside each title.<br />
								Click "Edit" to update an item or "Delete" to remove an existing entry.
							</p>
							<p>
								You may also search the list by typing in the search field and pressing enter or click the "Search" button.
							</p>
							<p>
								<strong>Add/Edit</strong><br />
								When adding or editing an existing item, select "Format" if you would like the text to be automatically
								formatted just as all post or page content blocks. Leave format unchecked if you would like to store the text
								without formatting.
							<p>
							'
		));
		$screen->add_help_tab(array(
			'id' => 'textbin_import-export',
			'title' => 'Import/Export',
			'content' => 	'<p>
								This feature allows you to export your current TextBin items to back them up or import then on another site.
								<br />Importing TextBin items <strong>will not</strong> replace your existing items.
							</p>
							<p>
								<strong>IMPORTANT:</strong> This feature only imports and exports the text information stored in an item and
								does not import or export any images, documents, assets, etc which may be linked to in the text content.
							</p>'
		));
	}
	
	
	
	/*
	 * Handles the various CRUD requests from the admin pages
	 */
	function admin_crud() {
		// add item
		if(isset($_POST['create_textBin'])) {
			if($this->create($_POST)) {
				$this->clearVars();
				$this->msg = 'TextBin Saved. &nbsp; <a href="' . $this->plugin_index . '">&larr; Back to List</a>';
			} else {
				$this->msg = 'Not Saved. There has been an error.';
			}
			
		}

		
		// update item
		if(isset($_POST['update_textBin'])) {
			$textbin = $this->read($_POST['textbinId']);
			if($textbin) {
				if($this->update($_POST)) {
					$this->msg = 'TextBin Updated. &nbsp; <a href="' . $this->plugin_index . '">&larr; Back to List</a>';
				} else {
					$this->msg = 'Not Updated. There has been an error.';
				}
			}
		}


		// delete item
		if(isset($_GET['id']) && $this->view == 'delete') {
			$textbin = $this->read($_GET['id']);
			$msg = 0;
			if($textbin) {
				if($this->del($textbin->id)) {
					$msg=1;
				}
			}
			header('Location: ' . $this->plugin_index . '&tbmsg=' . $msg);
		}



		// export all items
		if(isset($_POST['export_textBin'])) {
			$textbins = $this->readAll();
			if($textbins) {
				$file = array();
				foreach($textbins as $tb) {
					$file[] = $tb->name . $this->sep_field . $tb->val . $this->sep_field . $tb->filter . $this->sep_field . $tb->ord;
				}
				$text = implode($this->sep_row, $file);
				$file_name = 'export_' . date('Y-m-d-G-i-s') . '.txtbin';
				header('Content-disposition: attachment; filename='.$file_name);
				header('Content-type: text/plain');
				echo $text;
				die();
			} else {
				$this->msg = 'There are no TextBin items to export.';
			}
		}
		
		
		
		// import items
		if(isset($_POST['import_textBin']) && isset($_FILES['file']['tmp_name'])) {
			if(substr($_FILES['file']['name'], -7) == '.txtbin') {
				$text = file_get_contents($_FILES['file']['tmp_name']);
				$rows = explode($this->sep_row, $text);
				if($rows) {
					$names = array();
					foreach($rows as $row) {
						$fields = explode($this->sep_field, $row);
						if($fields) {
							if(count($fields) == 4) {
								$post['textbinName'] = $fields[0];
								$post['textbinVal'] = $fields[1];
								$post['textbinFilter'] = $fields[2];
								$post['textbinOrd'] = $fields[3];
								if($this->create($post)) {
									$names[] = $fields[0];
								}
							}
						}
					}
					if($names) {
						$this->msg = 'The following items have been imported:<br />' . implode(', ', $names); 
					} else {
						$this->msg = 'No items were imported';
					}
				} else {
					$this->msg = 'Sorry but the data contained in the file is in the wrong format.';
				}
			} else {
				$this->msg = 'Sorry but this file appears to be the wrong format.';
			}
		}
	}
	/*
	-------------------------------------------------------------------------
	END ADMIN SETUP
	-------------------------------------------------------------------------
	*/
	
	
	
	
	/*
	-------------------------------------------------------------------------
	ADMIN PAGES
	-------------------------------------------------------------------------
	*/
	/*
	 * Display the list page in the admin
	 */
	function admin() {
		if($this->view == 'index') {
			$textbins = $this->readAll();
			if(isset($_GET['tbmsg'])) {
				if($_GET['tbmsg'] == 1) {
					$this->msg = 'TextBin item deleted.';
				} else {
					$this->msg = 'TextBin item could not be deleted.';
				}
			}
			require_once('views/index.php');
		}
		
		
		if($this->view == 'edit') {
			$textbin = $this->read($_GET['id']);
			require_once('views/edit.php');
		}
	}
	
	
	/*
	 * Display the add page in the admin
	 */
	function admin_add() {
		require_once('views/add.php');
	}
	
	
	/*
	 * Display the import/export page in the admin
	 */
	function admin_port() {
		require_once('views/import_export.php');
	}
	
	
	/*
	 * Displays a list of results from an ajax text search
	 */
	function ajax_search() {
		global $wpdb;
		$term = (isset($_POST['term']) ? $wpdb->escape($_POST['term']) : false);
		
		if($term) {
			$textbins = $this->search($term);
		} else {
			$textbins = $this->readAll();
		}
		require_once('views/list.php');
		die();
	}
	/*
	-------------------------------------------------------------------------
	END ADMIN PAGES
	-------------------------------------------------------------------------
	*/
	
	
	
	/*
	-------------------------------------------------------------------------
	SHORTCODE
	-------------------------------------------------------------------------
	*/
	/*
	 * The shortcode for post content. Pass in the Name to retrieve the item.
	 * Pass in single=false to display a unordered list. Best when a single name
	 * has multiple entries
	 */
	function shortcode($atts=null) {
		if(empty($atts) || !isset($atts[0])) return;
		extract(shortcode_atts(array(
			'single' => 'true'
		), $atts));
		$single = ($single=='true' ? true : false);
		
		$results = $this->find($atts[0], $single);
		
		if($single) {
			$return = $results;
		} else {
			$return = "<ul class=\"tb-list\">";
			
			foreach($results as $result) {
				$return.= '<li>' . $result . '</li>';
			}
			$return.= "</ul>";
		}
		return $return;
	}
	/*
	-------------------------------------------------------------------------
	END SHORTCODE
	-------------------------------------------------------------------------
	*/
}





/*
 * TextBin widget. Themes must be widget ready to use the TextBin Widget
 */
class TextBinWidget extends WP_Widget {
	/**
	 * Construction method for adding widget to the WordPress admin.
	 */
	function TextBinWidget() {
		$widget_ops = array(
			'classname' => 'textbin_widget',
			'description' => __("Display a TextBin", 'textbin_widget')
		);
		$this->WP_Widget('textbin', __('TextBin', 'textbin_widget'), $widget_ops);
	}
	
	/**
	 * Formats a widgets content and echos it to the screen.
	 * 
	 * @param array $args					key/value array of WordPress widget default variables
	 * @param array $instance				key/value array of Widget options
	 */
	function widget($args, $instance) {
		global $textbin;
		extract($args);
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$name = empty($instance['name']) ? false : $instance['name'];
		$text = false;
		if($name) $text = $textbin->find($name);
		
		if($name) {
		?>
			<?php echo $before_widget; ?>
			<?php if($title) echo $before_title . $title . $after_title; ?>
			<?php echo $text; ?>
			<?php echo $after_widget; ?>
		<?php 
		}
	}
	
	/**
	 * Cleans input values before saving
	 * 
	 * @param array $new_instance			key/value array of form inputs
	 * @param array $old_instance			key/value array of available options
	 * @return array $instance				key/value array of cleaned form inputs
	 */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['name'] = strip_tags($new_instance['name']);
		return $instance;
	}
	
	/**
	 * Creation of Widget form for the Widgets page in WordPress admin.
	 * 
	 * @param array $instance				key/value array of saved fields
	 * @echo string							echos form to the admin screen
	 */
	function form($instance) {
		global $textbin;
		$title = esc_attr($instance['title']);
		$name = esc_attr($instance['name']);
		$items = $textbin->readAll();
		if($items):
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Title:'); ?>*
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
				type="text" value="<?php echo $title; ?>" />
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('name'); ?>">
				<?php _e('Name:'); ?><br />
				<select class="name" name="<?php echo $this->get_field_name('name'); ?>">
					<option value=""></option>
				<?php foreach($items as $item):
					$item_name = esc_attr($item->name);
					$select = ($item_name === $name ? 'selected' : '');
				?>
					<option value="<?php echo $item_name; ?>" <?php echo $select; ?>><?php echo $item_name; ?></option>
				<?php endforeach; ?>
				</select>
			</label>
		</p>
		<small><em>*optional</em></small>
		
		<?php else: ?>
		
		<p>Please add to TextBin to use this widget.</p>
		
		<?php 
		endif;
	}
}





/*
 * Register the TextBin Widget
 */
if(!function_exists('textbin_widget_register')) {
	function textbin_widget_register() {
		if(class_exists('TextBinWidget')) register_widget('TextBinWidget');
	}
}



/*
 * instantiate the textbin class
 * Register the activation class method, actions and shortcode.
 */
if(class_exists('TextBin')) {
	$textbin = new TextBin();

	register_activation_hook(__FILE__, array(&$textbin, 'init'));
	add_action('admin_menu', array(&$textbin, 'admin_setup'));
	add_action('admin_menu', array(&$textbin, 'admin_crud'));
	
	add_action("widgets_init", 'textbin_widget_register');
	
	add_shortcode('textbin', array(&$textbin, 'shortcode'));
	
	add_action('wp_ajax_tb_search', array(&$textbin, 'ajax_search'));
	add_action('wp_ajax_tb_reorder', array(&$textbin, 'reorder'));
}




/*
 * The textbin theme function. Use this to get a saved bit of text and display
 * in your theme.
 * 
 * @param string $title					Title of TextBin item to return
 * @param BOOL $single					True or false if TextBin item is single or multiple items
 * @param BOOL $echo					True or false if item should be returned or echoed
 * @echo or @return 					Echo or Return result(s)
 */
if(!function_exists('textbin')) {
	function textbin($title=null, $single=true, $echo = true) {
		global $textbin;
		$item = $textbin->find($title, $single);
		if($item) {
			if($single && $echo) {
				echo $item;
				return true;
			} else {
				return $item;
			}
		} else {
			return false;
		}
	}
}