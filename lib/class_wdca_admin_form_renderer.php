<?php
class Wdca_AdminFormRenderer {

    private $_mode_prefix;

    public function __construct($mode) {
        if (Wdca_Data::AB_MODE_KEY == $mode) $this->_mode_prefix = Wdca_Data::AB_MODE_KEY;
        else $this->_mode_prefix = Wdca_Data::get_valid_key($mode);
    }

    function _get_option($key = false) {
        $opts = get_option($this->_mode_prefix);
        if (!$key) return $opts;
        return isset($opts[$key]) ? $opts[$key] : '';
    }

    function _create_checkbox($name) {
        $pfx = $this->_mode_prefix;
        $opt = $this->_get_option();
        $value = isset($opt[$name]) ? $opt[$name] : '';

        $yesChecked = ((int)$value === 1) ? 'checked="checked"' : '';
        $noChecked = ((int)$value === 0) ? 'checked="checked"' : '';

        $html = "<input type='radio' name='{$pfx}[{$name}]' id='{$name}-yes' value='1' {$yesChecked} /> " .
            "<label for='{$name}-yes'>" . __('Ja', 'wdca') . "</label>" .
            '&nbsp;' .
            "<input type='radio' name='{$pfx}[{$name}]' id='{$name}-no' value='0' {$noChecked} /> " .
            "<label for='{$name}-no'>" . __('Nein', 'wdca') . "</label>";

        return $html;
    }

    function _create_textbox($name) {
        $pfx = $this->_mode_prefix;
        $value = (int)esc_attr($this->_get_option($name));
        return "<input type='text' size='2' maxsize='4' name='{$pfx}[{$name}]' id='{$pfx}-{$name}' value='{$value}' />";
    }

    function _create_text_inputbox($name, $label, $help = '', $pfx = 'wdca') {
        $pfx = $this->_mode_prefix;
        $value = esc_attr($this->_get_option($name));
        if ($help) $help = "<div><small>{$help}</small></div>";
        return
            "<label for='{$pfx}-{$name}'>{$label}</label> " .
            "<input type='text' class='widefat' name='{$pfx}[{$name}]' id='{$pfx}-{$name}' value='{$value}' />" .
            $help;
    }

	function _create_radiobox ($name, $value) {
		$pfx = $this->_mode_prefix;
		$opt = $this->_get_option($name);
		$checked = (@$opt == $value) ? true : false;
		return "<input type='radio' name='{$pfx}[{$name}]' id='{$name}-{$value}' value='{$value}' " . ($checked ? 'checked="checked" ' : '') . " /> ";
	}

	function create_enabled_box () {
		echo $this->_create_checkbox('enabled');
	}

	function create_live_mode_box () {
		echo $this->_create_checkbox('live_mode');
		echo '<div><small>' . __('Wenn Du diese Option deaktivierst, werden Deine Anzeigen nur angemeldeten Nutzern angezeigt', 'wdca') . '</small></div>';
		echo '<div>' . __('Schalte dies NICHT ein, bis Du bereit bist, live zu gehen', 'wdca') . '</div>';
	}

	function create_ad_count_box () {
		echo $this->_create_textbox('ad_count');
		echo '<div><small>' . __('So viele Anzeigen werden pro Beitragsseite geschaltet', 'wdca') . '</small></div>';
	}

	function create_ad_order_box () {
		$orders = array(
			'rand' => __('Zufällig', 'wdca'),
			'title' => __('Titel', 'wdca'),
			'date' => __('Datum', 'wdca'),
			'modified' => __('Bearbeitet', 'wdca'),
		);
		$bys = array('ASC', 'DESC');

		$opt_ord = $this->_get_option('ad_order');
		$opt_ord = $opt_ord ? $opt_ord : 'rand';
		$opt_ord_by = $this->_get_option('ad_order_by');
		$opt_ord_by = $opt_ord_by ? $opt_ord_by : 'ASC';

		echo '<select name="' . $this->_mode_prefix . '[ad_order]" id="wdca-ad_order">';
		foreach ($orders as $key=>$title) {
			$selected = ($opt_ord == $key) ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$title}</option>";
		}
		echo '</select>';
		echo '<select name="wdca[ad_order_by]" id="wdca-ad_order_by">';
		foreach ($bys as $key) {
			$selected = ($opt_ord_by == $key) ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$key}</option>";
		}
		echo '</select>';

		echo '<div><small>' . __('Deine Anzeigen werden so bestellt, wie Du sie hier eingerichtet hast', 'wdca') . '</small></div>';
	}

	function create_p_first_count_box () {
		echo $this->_create_textbox('p_first_count');
		echo '<div><small>' . __('Deine erste Anzeige wird nach so vielen Absätzen in den Beitrag eingefügt.', 'wdca') . '</small></div>';
	}
	function create_p_count_box () {
		echo $this->_create_textbox('p_count');
		echo '<div><small>' . __('Deine nachfolgenden Anzeigen werden alle [Anzahl] Absätze in den Beitrag eingefügt.', 'wdca') . '</small></div>';
	}

	function create_ad_show_after_box () {
		$predefined_delays = array(1,5) + range(5, 30, 5);
		$delay = $this->_get_option('ad_delay');
		$select = '<select name="' . $this->_mode_prefix . '[ad_delay]">';
		$select .= '<option value="">' . __('sofort', 'wdca') . '</option>';
		foreach ($predefined_delays as $count) {
			$selected = $count == $delay ? 'selected="selected"' : '';
			$label = $count != 1 
				? sprintf(__('%d Tage', 'wdca'), $count)
				: sprintf(__('%d Tag', 'wdca'), $count)
			;
			$select .= "<option value='{$count}' {$selected}>{$label}</option>";
		}
		$select .= '</select>';
		echo '<label>' . sprintf(__('Meine Anzeigen %s anzeigen, nachdem der Beitrag veröffentlicht wurde', 'wdca'), $select) . '</label>';
		echo '<div><small>' . __('Verwende diese Option, um die automatische Anzeigeninjektion für einen ausgewählten Zeitraum zu verzögern.', 'wdca') . '</small></div>';
	}

	function create_predefined_positions_box () {
		echo '' .
			__('Vor dem ersten Absatz:', 'wdca') .
			'&nbsp;' .
			$this->_create_checkbox('predefined_before_first_p') .
			'<div><small>' . __('Wenn Du diese Option aktivierst, wird Deine erste Anzeige ganz am Anfang des Beitrags eingefügt', 'wdca') . '</small></div>' .
		'<br />';
		echo '' .
			__('Auf halbem Weg durch den Beitrag:', 'wdca') .
			'&nbsp;' .
			$this->_create_checkbox('predefined_halfway_through') .
			'<div><small>' . __('Durch Aktivieren dieser Option wird eine Anzeige in der Mitte des Beitrags eingefügt', 'wdca') . '</small></div>' .
		'<br />';
		echo '' .
			__('Nach dem letzten Absatz:', 'wdca') .
			'&nbsp;' .
			$this->_create_checkbox('predefined_after_last_p') .
			'<div><small>' . __('Wenn Du diese Option aktivierst, wird die erste Anzeige ganz am Ende des Beitrags eingefügt', 'wdca') . '</small></div>' .
		'<br />';

		$ps = (int)$this->_get_option('predefined_ignore_other-paragraph_count');
		$paragraphs = "<input type='text' name='{$this->_mode_prefix}[predefined_ignore_other-paragraph_count]' size='2' value='{$ps}' />";
		echo '' .
			__('Ignoriere andere Einfügungseinstellungen:', 'wdca') .
			'&nbsp;' .
			$this->_create_checkbox('predefined_ignore_other') .
			'<div><small>' . __('Wenn Du diese Option aktivierst, werden andere infügungseinstellungen ignoriert und Ihre Anzeigen werden nur mit den ausgewählten vordefinierten Einstellungen eingefügt', 'wdca') . '</small></div>' .
			sprintf(__('... aber nur für Beiträge, die länger als %s Absätze sind', 'wdca'), $paragraphs) .
			'<div><small>' . __('Wenn Beiträge kürzer als die hier eingegebene Anzahl von Absätzen sind, hat das Standardverhalten Vorrang vor der vordefinierten Positionsinjektion.', 'wdca') . '</small></div>' .
			'<div><small>' . __('Lasse den Wert auf <code>0</code> um dieses Verhalten zu deaktivieren.', 'wdca') . '</small></div>' .
		'<br />';
	}

	function create_theme_box () {
		$themes = array(
			'' => __('Standard', 'wdca'),
			'wpmu' => __('PSOURCE', 'wdca'),
			'dark' => __('Dunkel', 'wdca'),
			'dotted' => __('Gepunktet', 'wdca'),
			'greenbutton' => __('Grüner Knopf', 'wdca'),
			'wpmu2013' => __('PSOURCE 2018', 'wdca'),
			'paper' => __('Papier (nur moderne Browser)', 'wdca'),
			//'alex' => __('Alex', 'wdca'),
		);
		$current = $this->_get_option('theme');

		echo '<select name="' . $this->_mode_prefix . '[theme]" id="wdca-theme">';
		foreach ($themes as $key => $lbl) {
			$selected = ($current == $key) ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$lbl}</option>";
		}
		echo '</select>';
	}

	function create_messages_box () {
		echo $this->_create_text_inputbox('msg_header', __('Headertext', 'wdca'), __('Dieser Text wird in der Anzeigenüberschrift vor dem Link angezeigt', 'wdca'));
		echo $this->_create_text_inputbox('msg_footer', __('Footertext', 'wdca'), __('Dieser Text wird unter dem Anzeigeninhalt vor dem Link angezeigt', 'wdca'));
		echo $this->_create_text_inputbox('msg_link', __('Footerlinktext', 'wdca'), __('Dieser Text wird als Linktext in der Fußzeile angezeigt', 'wdca'));
	}

	function create_link_box () {
		echo $this->_create_radiobox('link_target', '') .
			'&nbsp;' .
			'<label for="link_target-">' . __('Wird im aktuellen Fenster/Tab geöffnet', 'wdca') . '</label>' .
		'<br />';
		echo $this->_create_radiobox('link_target', 'blank') .
			'&nbsp;' .
			'<label for="link_target-blank">' . __('Öffnet in neuem Fenster/Tab', 'wdca') . '</label>' .
		'';
	}

	function create_ga_setup_box () {
		echo '<p><i>' .
			__('<b>Hinweis:</b> Deine Seite muss bereits für das Google Analytics-Tracking eingerichtet sein, damit dies ordnungsgemäß funktioniert.', 'wdca') .
		'</i></p>';
	}

	function create_ga_integration_box () {
		echo $this->_create_checkbox('ga_integration');
	}

	function create_ga_category_box () {
		$value = $this->_get_option('ga_category');
		$value = esc_attr((
			$value
				? $value
				: 'BeitragsAds'
		));
		echo "<input type='text' name='{$this->_mode_prefix}[ga_category]' value='{$value}' class='regular-text' />";
	}

	function create_ga_label_box () {
		$value = $this->_get_option('ga_label');
		if (!$value) $value = Wdca_Data::DEFAULT_KEY == $this->_mode_prefix ? 'Standard' : 'Group B';
		$value = esc_attr($value);
		echo "<input type='text' name='{$this->_mode_prefix}[ga_label]' value='{$value}' class='regular-text' />";
	}

	function create_selector_box () {
		$selector = $this->_get_option('selector');
		$selector = $selector ? $selector : '>p';
		echo "<input type='text' name='{$this->_mode_prefix}[selector]' id='wdca-selector' value='{$selector}' class='widefat' />" .
			'<div><small>' . __('Wenn Du Probleme mit Deinem Theme hast, kannst Du den Standard-Selektor auf einen allgemeineren ändern - z.B. <code>p</code>', 'wdca') . '</small></div>' .
			'<div><small>' . __('Du kannst dieses Feld auch verwenden, um das Einfügen von Anzeigen nach anderen Elementen zu ermöglichen - z.B. <code>ul,ol,p</code>', 'wdca') . '</small></div>' .
		'';
	}

	function create_cpt_ads_box () {
		$raw_types = get_post_types(array('public'=>true), 'objects');
		$types = array();
		$_skip_types = array('attachment', 'post', Wdca_CustomAd::POST_TYPE);
		foreach ($raw_types as $type) {
			if (in_array($type->name, $_skip_types)) continue;
			$types[$type->name] = $type->label;
		}
		$selected_types = $this->_get_option('custom_post_types');
		$selected_types = $selected_types ? $selected_types : array();

		echo '<select name="' . $this->_mode_prefix . '[custom_post_types][]" multiple="multiple">';
		foreach ($types as $key => $label) {
			$selected = in_array($key, $selected_types) ? 'selected="selected"' : '';
			echo "<option value='{$key}' {$selected}>{$label}</option>";
		}
		echo '</select>';
		echo '<div><small>' . __('Das Plugin fügt standardmäßig automatisch Anzeigen in Deine Beiträge ein. Wähle hier weitere Beitragstypen aus.', 'wdca') . '</small></div>';

		echo '' .
			'<label for="cpt_skip_posts-yes">' . __('Nicht automatisch in Beiträge injizieren:', 'wdca') . '</label>&nbsp;' .
			$this->_create_checkbox('cpt_skip_posts') .
		'';

		echo '<div><small>' . __('Diese Einstellungen gelten nur für das automatische Einfügen. Du kannst die Anzeigen weiterhin mithilfe von Shortcodes einfügen.', 'wdca') . '</small></div>';
	}

	function create_post_metabox_box () {
		echo $this->_create_checkbox('post_metabox');
		echo '<div><small>' . __('Durch Aktivieren dieser Option wird der Beitrags-Editor-Oberfläche eine Metabox hinzugefügt, mit der Du das Einfügen von Anzeigen pro Beitrag verhindern kannst.', 'wdca') . '</small></div>';
	}

	function create_categories_box () {
		$categories = apply_filters('wdca-settings-categories_list', get_terms('category', array('orderby'=>'term_group', 'hide_empty' => false)));
		$ad_terms = get_terms('wdca_ad_categories', array('orderby'=>'term_group', 'hide_empty' => false));

		$cats_to_ads = $this->_get_option('category_ads');
		$cats_to_ads = is_array($cats_to_ads) ? $cats_to_ads : array();

		$ad_str = $cat_str = '';

		$cat_str = '<select id="wdca_categories">';
		$cat_str .= "<option value=''></option>";
		foreach ($categories as $cat) {
			$cat_str .= "<option value='{$cat->term_id}'>{$cat->name}</option>";
			$ad_str .= "<div class='wdca_ads_to_cat' id='wdca_ads_to-cat-{$cat->term_id}' style='display:none'>";
			foreach ($ad_terms as $ad) {
				$checked = isset($cats_to_ads[$cat->term_id][$ad->term_id]) ? 'checked="checked"' : '';
				$ad_str .= "<input type='checkbox' name='{$this->_mode_prefix}[category_ads][{$cat->term_id}][{$ad->term_id}]' id='wdca_ad_to_cat_item-{$cat->term_id}-{$ad->term_id}' value='{$ad->term_id}' {$checked} />";
				$ad_str .= " <label for='wdca_ad_to_cat_item-{$cat->term_id}-{$ad->term_id}'>{$ad->name}</label><br />";
			}
			$ad_str .= '</div>';
		}
		$cat_str .= '</select>';

		//echo $cat_str . $ad_str;
		echo '<table class="widefat">';
		echo '<thead><tr><th>' . __('Meine Beiträge in dieser Kategorie', 'wdca') . '&hellip;</th><th>&hellip;' . __('zeigt nur Anzeigen aus diesen Anzeigenkategorien an', 'wdca') . '</th></tr></thead>';
		echo '<tfoot><tr><th></th><th></th></tr></tfoot>';
		echo "<tbody><tr><td>{$cat_str}</td><td>{$ad_str}</td></tr></tbody>";
		echo '</table>';
		_e('Wenn Du hier keine Zuordnungen festlegst, kann jede Anzeige in einem der Beiträge erscheinen.', 'wdca');
		echo <<<EOMappingJs
		<script type="text/javascript">
		(function ($) {
		$(function () {
		
		function toggle_ads_to_cats () {
			var cat = $("#wdca_categories").val();
			var root = $("#wdca_ads_to-cat-" + cat);
			if (!root.length) return false;
			$(".wdca_ads_to_cat").hide();
			root.show();
		}
		
		$("#wdca_categories").on('change', toggle_ads_to_cats);
		toggle_ads_to_cats();
		
		});
		})(jQuery);
		</script>
		EOMappingJs;
	}

	function create_tags_box () {
		$tags = apply_filters('wdca-settings-tags_list', get_terms('post_tag', array('orderby'=>'term_group', 'hide_empty' => false)));
		$ad_terms = get_terms('wdca_ad_categories', array('orderby'=>'term_group', 'hide_empty' => false));

		$tags_to_ads = $this->_get_option('tag_ads');
		$tags_to_ads = is_array($tags_to_ads) ? $tags_to_ads : array();

		$ad_str = $tag_str = '';

		$tag_str = '<select id="wdca_tags">';
		$tag_str .= "<option value=''></option>";
		foreach ($tags as $tag) {
			$tag_str .= "<option value='{$tag->term_id}'>{$tag->name}</option>";
			$ad_str .= "<div class='wdca_ads_to_tag' id='wdca_ads_to-tag-{$tag->term_id}' style='display:none'>";
			foreach ($ad_terms as $ad) {
				$checked = isset($tags_to_ads[$tag->term_id][$ad->term_id]) ? 'checked="checked"' : '';
				$ad_str .= "<input type='checkbox' name='{$this->_mode_prefix}[tag_ads][{$tag->term_id}][{$ad->term_id}]' id='wdca_ad_to_cat_item-{$tag->term_id}-{$ad->term_id}' value='{$ad->term_id}' {$checked} />";
				$ad_str .= " <label for='wdca_ad_to_tag_item-{$tag->term_id}-{$ad->term_id}'>{$ad->name}</label><br />";
			}
			$ad_str .= '</div>';
		}
		$tag_str .= '</select>';

		//echo $cat_str . $ad_str;
		echo '<table class="widefat">';
		echo '<thead><tr><th>' . __('Meine Beiträge in diesem Tag', 'wdca') . '&hellip;</th><th>&hellip;' . __('zeigt nur Anzeigen aus diesen Anzeigenkategorien an', 'wdca') . '</th></tr></thead>';
		echo '<tfoot><tr><th></th><th></th></tr></tfoot>';
		echo "<tbody><tr><td>{$tag_str}</td><td>{$ad_str}</td></tr></tbody>";
		echo '</table>';
		_e('Wenn Du hier keine Zuordnungen festlegst, kann jede Anzeige in einem der Beiträge erscheinen.', 'wdca');
		echo <<<EOMappingJs
		<script type="text/javascript">
		(function ($) {
		$(function () {
		
		function toggle_ads_to_tags () {
			var tag = $("#wdca_tags").val();
			var root = $("#wdca_ads_to-tag-" + tag);
			if (!root.length) return false;
			$(".wdca_ads_to_tag").hide();
			root.show();
		}
		
		$("#wdca_tags").on('change', toggle_ads_to_tags);
		toggle_ads_to_tags();
		
		});
		})(jQuery);
		</script>
		EOMappingJs;
	}

	function create_lazy_loading_box () {
		echo __('Aktiviere das verzögerte Laden von Abhängigkeiten:', 'wdca') .
			'&nbsp;' .
			$this->_create_checkbox('enable_late_binding') .
			'<div><small>' . __('Das verzögerte Laden von Abhängigkeiten kann die Ladezeiten Deiner Seite verbessern, indem Ressourcen nach Bedarf benötigt werden.', 'wdca') . '</small></div>'
		;

		$wdca = Wdca_CustomAd::get_instance();
		$hook = $wdca->get_late_binding_hook();
		echo '<br />' .
			'<label for="wdca-late_binding_hook">' . __('Verzögertes Laden Hook <small>(fortgeschritten)</small>:', 'wdca') . '</label>&nbsp;' .
			'<input type="text" name="' . $this->_mode_prefix . '[late_binding_hook]" id="wdca-late_binding_hook" value="' . $hook . '" />' .
			'<div><small>' . __('Das verzögerte Laden von Abhängigkeiten hängt vom Fußzeilenhook ab, um ordnungsgemäß bereitgestellt zu werden. Wenn Dein Design den Standard-Hook nicht implementiert, verwende dieses Feld, um einen benutzerdefinierten festzulegen.', 'wdca') . '</small></div>'
		;

		echo '<h4>' . __('Stileinschlussart', 'wdca') . '</h4>' .
			$this->_create_radiobox('style_inclusion_type', '') . '&nbsp;<label for="style_inclusion_type-">' . __('Normal', 'wdca') . '</label><br />' .
			$this->_create_radiobox('style_inclusion_type', 'inline') . '&nbsp;<label for="style_inclusion_type-inline">' . __('Inline', 'wdca') . '</label><br />' .
			$this->_create_radiobox('style_inclusion_type', 'dynamic') . '&nbsp;<label for="style_inclusion_type-dynamic">' . __('Dynamisch', 'wdca') . '</label><br />' .
		'';
	}


	function create_ab_mode_setup_box () {
		echo '<p><i>' .
			__('Hier kannst Du Deine A/B-Tests einrichten und die Laderegeln für Gruppen einstellen.', 'wdca') .
		'</i></p>';
	}

	function create_sessions_box () {
		echo $this->_create_checkbox('remember_in_session');
		echo '<div><small>' .
			__('Standardmäßig ist die Verteilung im A/B-Modus zufällig. Durch Aktivieren dieser Option wird der ursprünglich ausgewählte Modus für Deine Benutzer erzwungen, damit sie über Anforderungen hinweg bestehen bleiben (d. H. Benutzer, die Einstellungen für den A-Modus erhalten haben, werden diese weiterhin sehen und umgekehrt).', 'wdca') .
		'</small></div>';
	}

	function create_b_group_for_admins_box () {
		echo $this->_create_checkbox('b_group_for_admins');
	}
	
	function create_b_group_for_users_box () {
		echo $this->_create_checkbox('b_group_for_users');
	}

	function create_get_key_override_box () {
		echo $this->_create_checkbox('allow_get_key_override');
		echo '<div><small>' .
			__('Wenn der A/B-Test aktiviert ist und Du diese Option zulässt, kannst Du jede Gruppe bedingungslos testen, indem Du dies an die URL übergibst: <code>?wdca_mode=a</code> für A Gruppen Einstellungen, <code>?wdca_mode=b</code> für B Gruppen Einstellungen.', 'wdca') .
		'</small></div>';
	}


}