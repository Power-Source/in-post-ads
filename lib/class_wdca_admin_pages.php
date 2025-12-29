<?php
/**
 * Handles all Admin access functionality.
 */
class Wdca_AdminPages {

	function __construct () {}

	/**
	 * Main entry point.
	 *
	 * @static
	 */
	public static function serve () {
		$me = new Wdca_AdminPages;
		$me->add_hooks();
	}

	function create_admin_menu_entry () {
		if (@$_POST && isset($_POST['option_page'])) {
			$changed = false;
			$page = !empty($_GET['page']) ? $_GET['page'] : false;
			$key = Wdca_Data::AB_MODE_KEY == $page
				? Wdca_Data::AB_MODE_KEY
				: Wdca_Data::get_valid_key($page)
			;
			if ("{$key}-options" == @$_POST['option_page']) {
				update_option($key, stripslashes_deep($_POST[$key]));
				$changed = true;
			}

			if ($changed) {
				$goback = add_query_arg('settings-updated', 'true',  wp_get_referer());
				wp_redirect($goback);
				die;
			}
		}
		/*$page = is_multisite() ? 'settings.php' : 'options-general.php';*/
		$page = "edit.php?post_type=" . Wdca_CustomAd::POST_TYPE;
		$perms = defined('WDCA_LEGACY_OPTIONS_ACCESS') && WDCA_LEGACY_OPTIONS_ACCESS
			? is_multisite() ? 'manage_network_options' : 'manage_options'
			: (is_multisite() && !defined('WDCA_MINIMUM_ADMIN_CAPABILITY') ? 'manage_options' : (defined('WDCA_MINIMUM_ADMIN_CAPABILITY') ? WDCA_MINIMUM_ADMIN_CAPABILITY : 'manage_options'))
		;
		if (!Wdca_Data::get_ab_option('enabled')) {
			add_submenu_page($page, __('Einstellungen', 'wdca'), __('Einstellungen', 'wdca'), $perms, 'wdca', array($this, 'create_admin_page'));
		} else {
			add_submenu_page($page, __('Einstellungen (A)', 'wdca'), __('Einstellungen (A)', 'wdca'), $perms, Wdca_Data::DEFAULT_KEY, array($this, 'create_admin_page'));
			add_submenu_page($page, __('Einstellungen (B)', 'wdca'), __('Einstellungen (B)', 'wdca'), $perms, Wdca_Data::B_GROUP_KEY, array($this, 'create_admin_page'));
		}
		add_submenu_page($page, __('A/B Einstellungen', 'wdca'), __('A/B Einstellungen', 'wdca'), $perms, Wdca_Data::AB_MODE_KEY, array($this, 'create_admin_page'));
		
	}

	function register_settings () {
		// Register AB settings
		$mode = Wdca_Data::AB_MODE_KEY;
		$form = new Wdca_AdminFormRenderer($mode);
		register_setting($mode, $mode);
		add_settings_section('wdca_settings', __('A/B Modus Einrichtung', 'wdca'), array($form, 'create_ab_mode_setup_box'), "{$mode}-options");
		add_settings_field('wdca_enable', __('Aktiviere A/B Test', 'wdca'), array($form, 'create_enabled_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_session', __('Track-Modus in Sessions', 'wdca'), array($form, 'create_sessions_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_b_group_for_admins', __('Zeige Admins immer die B-Gruppe', 'wdca'), array($form, 'create_b_group_for_admins_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_b_group_for_users', __('Zeige allen Benutzern immer die Gruppe B an', 'wdca'), array($form, 'create_b_group_for_users_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_get_override', __('GET key override', 'wdca'), array($form, 'create_get_key_override_box'), "{$mode}-options", 'wdca_settings');

		// ... that's done. Now, register mode settings:
		if (!Wdca_Data::get_ab_option('enabled')) return $this->register_mode_settings(Wdca_Data::DEFAULT_KEY);

		$this->register_mode_settings(Wdca_Data::DEFAULT_KEY);
		$this->register_mode_settings(Wdca_Data::B_GROUP_KEY);
	}

	function register_mode_settings ($mode) {
		$form = new Wdca_AdminFormRenderer($mode);

		register_setting($mode, $mode);
		add_settings_section('wdca_settings', __('Benutzerdefinierte Anzeigen', 'wdca'), function() {}, "{$mode}-options");
		add_settings_field('wdca_enable', __('Aktiviere Benutzerdefinierte Anzeigen', 'wdca'), array($form, 'create_enabled_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_test', __('Live Modus', 'wdca'), array($form, 'create_live_mode_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_ad_count', __('Zeige so viele Anzeigen', 'wdca'), array($form, 'create_ad_count_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_ad_order', __('Ordne Anzeigen nach', 'wdca'), array($form, 'create_ad_order_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_p_first_count', __('Füge nach so vielen Absätzen die erste Anzeige ein', 'wdca'), array($form, 'create_p_first_count_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_p_count', __('Füge nach so vielen Absätzen jeweils nachfolgende Anzeigen ein', 'wdca'), array($form, 'create_p_count_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_ad_delay', __('Verzögertes Einfügen von Anzeigen', 'wdca'), array($form, 'create_ad_show_after_box'), "{$mode}-options", 'wdca_settings');
		add_settings_field('wdca_predefined_positions', __('Vordefinierte Positionen', 'wdca'), array($form, 'create_predefined_positions_box'), "{$mode}-options", 'wdca_settings');

		add_settings_section('wdca_appearance', __('Aussehen &amp; Mitteilungen', 'wdca'), function() {}, "{$mode}-options");
		add_settings_field('wdca_theme', __('Theme', 'wdca'), array($form, 'create_theme_box'), "{$mode}-options", 'wdca_appearance');
		add_settings_field('wdca_messages', __('Mitteilungen', 'wdca'), array($form, 'create_messages_box'), "{$mode}-options", 'wdca_appearance');
		add_settings_field('wdca_link', __('Linkklick', 'wdca'), array($form, 'create_link_box'), "{$mode}-options", 'wdca_appearance');
		
		add_settings_section('wdca_analytics', __('Google Analytics Integration', 'wdca'), array($form, 'create_ga_setup_box'), "{$mode}-options");
		add_settings_field('wdca_ga_integration', __('Aktiviere Google Analytics integration', 'wdca'), array($form, 'create_ga_integration_box'), "{$mode}-options", 'wdca_analytics');
		add_settings_field('wdca_ga_category', __('Ereigniskategorie', 'wdca'), array($form, 'create_ga_category_box'), "{$mode}-options", 'wdca_analytics');
		add_settings_field('wdca_ga_label', __('Ereignislabel', 'wdca'), array($form, 'create_ga_label_box'), "{$mode}-options", 'wdca_analytics');

		add_settings_section('wdca_advanced', __('Erweitert', 'wdca'), function() {}, "{$mode}-options");
		add_settings_field('wdca_allow_post_types', __('Benutzerdefinierte Beitragstypen Anzeigen', 'wdca'), array($form, 'create_cpt_ads_box'), "{$mode}-options", 'wdca_advanced');
		add_settings_field('wdca_post_metabox', __('Beitrag Metabox anzeigen', 'wdca'), array($form, 'create_post_metabox_box'), "{$mode}-options", 'wdca_advanced');
		add_settings_field('wdca_to_categories', __('Verknüpfe Beitragskategorien', 'wdca'), array($form, 'create_categories_box'), "{$mode}-options", 'wdca_advanced');
		add_settings_field('wdca_to_tags', __('Verknüpfe Beitrags-Tags', 'wdca'), array($form, 'create_tags_box'), "{$mode}-options", 'wdca_advanced');
		add_settings_field('wdca_elements', __('Selector', 'wdca'), array($form, 'create_selector_box'), "{$mode}-options", 'wdca_advanced');
		add_settings_field('wdca_lazy_loading', __('Verzögertes Laden', 'wdca'), array($form, 'create_lazy_loading_box'), "{$mode}-options", 'wdca_advanced');
	}

	function create_admin_page () {
		$option_key = $title = false;
		if (!empty($_GET['page']) && Wdca_Data::AB_MODE_KEY == $_GET['page']) {
			$option_key = Wdca_Data::AB_MODE_KEY;
			$title = __('A/B Einstellungen', 'wdca');
		} else {
			$option_key = Wdca_Data::get_ab_option('enabled') 
				? Wdca_Data::get_valid_key(@$_GET['page']) 
				: Wdca_Data::DEFAULT_KEY
			;
			$title = !Wdca_Data::get_ab_option('enabled')
				? __('Einstellungen', 'wdca')
				: (Wdca_Data::DEFAULT_KEY == Wdca_Data::get_valid_key(@$_GET['page'])
					? __('Einstellungen (A)', 'wdca')
					: __('Einstellungen (B)', 'wdca')
				)
			;
		}
		include(WDCA_PLUGIN_BASE_DIR . '/lib/forms/plugin_settings.php');
	}

	function js_print_scripts () {
		printf(
			'<script type="text/javascript">
				var _wdca_data = {
					"root_url": "%s",
				};
			</script>',
			WDCA_PLUGIN_URL
		);
	}

	function js_editor_button () {
		wp_enqueue_script('wdca_editor', WDCA_PLUGIN_URL . '/js/wdca-button.js', array('jquery'));
		wp_localize_script('wdca_editor', 'l10nWdca', array(
			'add_ad' => __('Anzeige einfügen', 'wdca'),
			'ad_title' => __('Titel', 'wdca'),
			'ad_date' => __('Datum', 'wdca'),
			'appearance' => __('Aussehen', 'wdca'),
			'add_blank' => __('Füge einen leeren Platzhalter für eine Anzeige ein', 'wdca'),
			'or_select_below' => __('oder wähle eine Anzeige zum Einfügen aus den unten aufgeführten aus', 'wdca'),
			'dflt' => __('Standard', 'wdca'),
			'ad_size' => __('Größe', 'wdca'),
			'small' => __('Klein', 'wdca'),
			'medium' => __('Mittel', 'wdca'),
			'large' => __('Groß', 'wdca'),
			'ad_position' => __('Position', 'wdca'),
			'left' => __('Links', 'wdca'),
			'right' => __('Rechts', 'wdca'),
		));
	}

	function css_print_styles () {
	}

	public function add_meta_boxes () {
		add_meta_box(
			'wdca_prevent_ad_insertion',
			__('Anzeigen', 'wdca'),
			array($this, 'render_prevent_ad_box'),
			'post',
			'side',
			'low'
		);
	}

	public function render_prevent_ad_box () {
		global $post;
		$post_id = wp_is_post_revision($post);
		$post_id = $post_id ? $post_id : $post->ID;

		$opts = get_option('wdca');
		$prevent_items = @$opts['prevent_items'];
		$prevent_items = is_array($prevent_items) ? $prevent_items : array();
		$checked = in_array($post_id, $prevent_items) ? 'checked="checked"' : '';
		echo "<p><input type='checkbox' {$checked} name='wdca_hide_box' id='wdca_hide_box' value='1' />";
		echo ' <label for="wdca_hide_box">' . __('Beitragsanzeigen in diesem Beitrag nicht anzeigen', 'wdca') . '</label></p>';
	}

	function save_meta () {
		global $post;

		$post_id = wp_is_post_revision($post);
		$post_id = $post_id ? $post_id : (is_object($post) ? $post->ID : false);
		if (empty($post_id)) return false;

		$opts = get_option('wdca');
		$opts = $opts ? $opts : array();
		$opts['prevent_items'] = @$opts['prevent_items'] ? $opts['prevent_items'] : array();

		if (isset($_POST['wdca_hide_box'])) {
			$opts['prevent_items'][] = $post_id;
		} else {
			$key = array_search($post_id, $opts['prevent_items']);
			if (false !== $key) unset($opts['prevent_items'][$key]);
		}
		$opts['prevent_items'] = array_unique($opts['prevent_items']);
		update_option('wdca', $opts);
	}

	/**
	 * Handles ad listing requests.
	 */
	function json_list_ads () {
		$ads = Wdca_CustomAd::get_all_ads();
		header('Content-type: application/json');
		echo json_encode($ads);
		exit();
	}

	function add_hooks () {
		add_action('admin_init', array($this, 'register_settings'));
		$hook = /*is_multisite() ? 'network_admin_menu' :*/ 'admin_menu';
		add_action($hook, array($this, 'create_admin_menu_entry'));

		add_action('admin_init', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'save_meta'));

		add_action('admin_print_scripts', array($this, 'js_print_scripts'));
		add_action('admin_print_styles', array($this, 'css_print_styles'));

		add_action('admin_print_scripts-post.php', array($this, 'js_editor_button'));
		add_action('admin_print_scripts-post-new.php', array($this, 'js_editor_button'));

		add_action('wp_ajax_wdca_list_ads', array($this, 'json_list_ads'));

	}
}
