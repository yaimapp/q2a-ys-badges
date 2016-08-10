<?php

class qa_ys_badge_admin
{
	private $badges;

	function __construct()
	{
		$this->badges = ys_get_badge_list();
	}

	function allow_template($tamplate)
	{
		return ($template !== 'admin');
	}

	function option_default($option)
	{
		$slug = preg_replace('/ys_badge_(.+)_[^_]+/',"$1",$option);

		switch($option) {
			case 'ys_badge_'.$slug.'_name':
				return qa_lang('ys_badges/'.$slug);
			case 'ys_badge_'.$slug.'_var':
				return @$this->badges[$slug]['var'];
			case 'ys_badge_'.$slug.'_enabled':
				return '0';
			case 'ys_custom_badges':
				return true;
			case 'ys_notify_time':
				return 0;
			case 'ys_badge_widget_list_max':
				return 5;
			case 'ys_badge_widget_data_max':
				return 30;
			case 'ys_badge_email_subject':
				return '[^site_title]':
			case 'ys_badge_email_body':
				return 'Congratulations! You have earned a "^badge_name" badge from ^site_title ^if_post_text="for the following post:

^post_title
^post_url"

Please log in and visit your profile:

^profile_url

You may cancel these notices at any time by visiting your profile at the link above.';
			case 'ys_badges_css':
				return '.notify-container {
	left: 0;
	right: 0;
	top: 0;
	padding: 0;
	position: fixed;
	width: 100%;
	z-index: 10000;
}
.ys-badge-container-badge {
	white-space: nowrap;
}
.ys-badge-notify {
	background-color: #F6DF30;
	color: #444444;
	font-weight: bold;
	width: 100%;
	text-align: center;
	font-family: sans-serif;
	font-size: 14px;
	padding: 10px 0;
	position:relative;
}
.notify-close {
	color: #735005;
	cursor: pointer;
	font-size: 18px;
	line-height: 18px;
	padding: 0 3px;
	position: absolute;
	right: 8px;
	text-decoration: none;
	top: 8px;
}
#badge-form td {
	vertical-align:top;
}
.ys-badge-bronze,.ys-badge-silver, .ys-badge-gold {
	margin-right:4px;
	color: #000;
	font-weight:bold;
	text-align:center;
	border-radius:4px;
	width:120px;
	padding: 5px 10px;
	display: inline-block;
}
.ys-badge-bronze {
	background-color: #CB9114;

	background-image: -webkit-linear-gradient(left center , #CB9114, #EDB336, #CB9114, #A97002, #CB9114);
	background-image:    -moz-linear-gradient(left center , #CB9114, #EDB336, #CB9114, #A97002, #CB9114);
	background-image:     -ms-linear-gradient(left center , #CB9114, #EDB336, #CB9114, #A97002, #CB9114);
	background-image:      -o-linear-gradient(left center , #CB9114, #EDB336, #CB9114, #A97002, #CB9114);
	background-image:         linear-gradient(left center , #CB9114, #EDB336, #CB9114, #A97002, #CB9114); /* standard, but currently unimplemented */

	border:2px solid #6C582C;
}
.ys-badge-silver {
	background-color: #CDCDCD;
	background-image: -webkit-linear-gradient(left center , #CDCDCD, #EFEFEF, #CDCDCD, #ABABAB, #CDCDCD);
	background-image:    -moz-linear-gradient(left center , #CDCDCD, #EFEFEF, #CDCDCD, #ABABAB, #CDCDCD);
	background-image:     -ms-linear-gradient(left center , #CDCDCD, #EFEFEF, #CDCDCD, #ABABAB, #CDCDCD);
	background-image:      -o-linear-gradient(left center , #CDCDCD, #EFEFEF, #CDCDCD, #ABABAB, #CDCDCD);
	background-image:         linear-gradient(left center , #CDCDCD, #EFEFEF, #CDCDCD, #ABABAB, #CDCDCD); /* standard, but currently unimplemented */
	border:2px solid #737373;
}
.ys-badge-gold {
	background-color: #EEDD0F;
	background-image: -webkit-linear-gradient(left center , #EEDD0F, #FFFF2F, #EEDD0F, #CCBB0D, #EEDD0F);
	background-image:    -moz-linear-gradient(left center , #EEDD0F, #FFFF2F, #EEDD0F, #CCBB0D, #EEDD0F);
	background-image:     -ms-linear-gradient(left center , #EEDD0F, #FFFF2F, #EEDD0F, #CCBB0D, #EEDD0F);
	background-image:      -o-linear-gradient(left center , #EEDD0F, #FFFF2F, #EEDD0F, #CCBB0D, #EEDD0F);
	background-image:         linear-gradient(left center , #EEDD0F, #FFFF2F, #EEDD0F, #CCBB0D, #EEDD0F); /* standard, but currently unimplemented */
	border:2px solid #7E7B2A;
}
.ys-badge-bronze-medal, .ys-badge-silver-medal, .ys-badge-gold-medal  {
	font-size: 14px;
	font-family:sans-serif;
}
.ys-badge-bronze-medal {
	color: #CB9114;
}
.ys-badge-silver-medal {
	color: #CDCDCD;
}
.ys-badge-gold-medal {
	color: #EEDD0F;
}
.ys-badge-pointer {
	cursor:pointer;
}
.ys-badge-desc {
	padding-left:8px;
}
.ys-badge-count {
	font-weight:bold;
}
.ys-badge-count-link {
	cursor:pointer;
	color:#992828;
}
.ys-badge-source {
	text-align:center;
	padding:0;
}
.ys-badge-widget-entry {
	white-space:nowrap;
}
';
			default:
				return null;
		}
	}

	function admin_form(&$qa_content)
	{
		$ok = null;

		if(qa_clicked('ys_badge_save_settings')) {
			qa_opt('ys_badge_active', (bool)qa_post_text('ys_badge_active_check'));

			if (qa_opt('ys_badge_active')) {

				ys_badge_db::create_userbadges_table();
				ys_badge_db::create_achievements_table();

				// set badge names, vars and states

				foreach ($this->badges as $slug => $info) {
					// update var

					if(isset($info['var']) && qa_post_text('ys_badge_'.$slug.'_var')) {
						qa_opt('ys_badge_'.$slug.'_var',qa_post_text('ys_badge_'.$slug.'_var'));
					}

					// toggle activation

					if((bool)qa_post_text('ys_badge_'.$slug.'_enabled') === false) {
						qa_opt('ys_badge_'.$slug.'_enabled','0');
					}
					else qa_opt('ys_badge_'.$slug.'_enabled','1');

					// set custom names

					if (qa_post_text('ys_badge_'.$slug.'_edit') != qa_opt('ys_badge_'.$slug.'_name')) {
						qa_opt('ys_badge_'.$slug.'_name',qa_post_text('ys_badge_'.$slug.'_edit'));
						$qa_lang_default['ys_badges'][$slug] = qa_opt('ys_badge_'.$slug.'_name');
					}

				}

				// options

				qa_opt('ys_badge_notify_time', (int)qa_post_text('ys_badge_notify_time'));
				qa_opt('ys_badge_show_users_badges', (bool)qa_post_text('ys_badge_show_users_badges'));
				qa_opt('ys_badge_show_source_posts', (bool)qa_post('ys_badge_show_source_posts'));
				qa_opt('ys_badge_show_source_users',(bool)qa_post_text('ys_badge_show_source_users'));

				qa_opt('ys_badge_admin_user_widget',(bool)qa_post_text('ys_badge_admin_user_widget'));
				qa_opt('ys_badge_admin_loggedin_widget',(bool)qa_post_text('ys_badge_admin_loggedin_widget'));
				qa_opt('ys_badge_admin_user_widget_q_item',(bool)qa_post_text('ys_badge_admin_user_widget_q_item'));
				qa_opt('ys_badge_admin_user_field',(bool)qa_post_text('ys_badge_admin_user_field'));
				qa_opt('ys_badge_admin_user_field_no_tab',(bool)qa_post_text('ys_badge_admin_user_field_no_tab'));

				qa_opt('ys_badge_widget_date_max',(int)qa_post_text('ys_badge_widget_date_max'));
				qa_opt('ys_badge_widget_list_max',(int)qa_post_text('ys_badge_widget_list_max'));

				qa_opt('ys_badge_email_notify',(bool)qa_post_text('ys_badge_email_notify'));
				qa_opt('ys_badge_email_notify_on',(bool)qa_post_text('ys_badge_email_notify_on'));
				qa_opt('ys_badge_email_subject',qa_post_text('ys_badge_email_subject'));
				qa_opt('ys_badge_email_body',qa_post_text('ys_badge_email_body'));

				qa_opt('ys_badges_css', qa_post_text('ys_badges_css'));
			}
			$ok = qa_lang('ys_badges/badge_admin_saved');
		}

		//	Create the form for display
		$fields = array();

		$fields[] = array(
			'label' => qa_lang('ys_badges/badge_admin_activate'),
			'tags' => 'NAME="ys_badge_active_check"',
			'value' => qa_opt('ys_badge_active'),
			'type' => 'checkbox',
		);

		if(qa_opt('ys_badge_active')) {

			$fields[] = array(
					'label' => qa_lang('ys_badges/active_badges').':',
					'type' => 'static',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_admin_select_all'),
				'tags' => 'onclick="var isx = this.checked; jQuery(\'.ys-badge-listing :checkbox\').prop(\'checked\',isx);"',
				'value' => false,
				'type' => 'checkbox',
			);
			$badges = ys_get_badge_list();
			error_log(serialize($badges));
			foreach ($badges as $slug => $info) {

				$badge_name = ys_badge_name($slug);
				if(!qa_opt('ys_badge_'.$slug.'_name')) qa_opt('ys_badge_'.$slug.'_name',$badge_name);
				$name = qa_opt('ys_badge_'.$slug.'_name');

				$badge_desc = ys_badge_desc_replace($slug,qa_opt('ys_badge_'.$slug.'_var'),true);

				$level = ys_get_badge_level($info['level']);
				$levels = $level['slug'];

				$fields[] = array(
						'type' => 'static',
						'note' => '<table class="ys-badge-listing"><tr><td><input type="checkbox" name="ys_badge_'.$slug.'_enabled"'.(qa_opt('ys_badge_'.$slug.'_enabled') !== '0' ? ' checked':'').'></td><td><input type="text" name="ys_badge_'.$slug.'_edit" id="ys_badge_'.$slug.'_edit" style="display:none" size="16" onblur="ys_badgeEdit(\''.$slug.'\',true)" value="'.$name.'"><span id="ys_badge_'.$slug.'_badge" class="ys-badge-'.$levels.'" onclick="ys_badgeEdit(\''.$slug.'\')" title="'.qa_lang('ys_badges/badge_admin_click_edit').'">'.$name.'</span></td><td>'.$badge_desc.'</td></tr></table>'
				);
			}

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
					'label' => qa_lang('ys_badges/notify_time').':',
					'type' => 'number',
					'value' => qa_opt('ys_badge_notify_time'),
					'tags' => 'NAME="ys_badge_notify_time"',
					'note' => '<em>'.qa_lang('ys_badges/notify_time_desc').'</em>',
			);

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_admin_user_field'),
				'tags' => 'NAME="ys_badge_admin_user_field"',
				'value' => (bool)qa_opt('ys_badge_admin_user_field'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_admin_user_field_no_tab'),
				'tags' => 'NAME="ys_badge_admin_user_field_no_tab"',
				'value' => (bool)qa_opt('ys_badge_admin_user_field_no_tab'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_show_source_posts'),
				'tags' => 'NAME="ys_badge_show_source_posts"',
				'value' => (bool)qa_opt('ys_badge_show_source_posts'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_show_source_users'),
				'tags' => 'NAME="ys_badge_show_source_users"',
				'value' => (bool)qa_opt('ys_badge_show_source_users'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_admin_user_widget'),
				'tags' => 'NAME="ys_badge_admin_user_widget"',
				'value' => (bool)qa_opt('ys_badge_admin_user_widget'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_admin_user_widget_q_item'),
				'tags' => 'NAME="ys_badge_admin_user_widget_q_item"',
				'value' => (bool)qa_opt('ys_badge_admin_user_widget_q_item'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_admin_loggedin_widget'),
				'tags' => 'NAME="ys_badge_admin_loggedin_widget"',
				'value' => (bool)qa_opt('ys_badge_admin_loggedin_widget'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_show_users_badges'),
				'tags' => 'NAME="ys_badge_show_users_badges"',
				'value' => (bool)qa_opt('ys_badge_show_users_badges'),
				'type' => 'checkbox',
			);
			if (qa_clicked('ys_badge_trigger_notify')) {
				$fields['test-notify'] = 1;
			}

			$fields[] = array(
				'label' => 'Ys Badge css stylesheet',
				'tags' => 'NAME="ys_badges_css"',
				'value' => qa_opt('ys_badges_css'),
				'rows' => 20,
				'type' => 'textarea',
			);

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
					'label' => qa_lang('ys_badges/widget_list_max').':',
					'type' => 'number',
					'value' => qa_opt('ys_badge_widget_list_max'),
					'tags' => 'NAME="ys_badge_widget_list_max"',
			);

			$fields[] = array(
					'label' => qa_lang('ys_badges/widget_date_max').':',
					'type' => 'number',
					'value' => qa_opt('ys_badge_widget_date_max'),
					'tags' => 'NAME="ys_badge_widget_date_max"',
			);
			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_email_notify'),
				'tags' => 'NAME="ys_badge_email_notify" onclick="if(this.checked) jQuery(\'#badge_email_container\').fadeIn(); else jQuery(\'#badge_email_container\').fadeOut();"',
				'value' => (bool)qa_opt('ys_badge_email_notify'),
				'type' => 'checkbox',
				'note' => '<table id="ys_badge_email_container" style="display:'.(qa_opt('ys_badge_email_notify')?'block':'none').'"><tr><td>',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_email_notify_on'),
				'tags' => 'NAME="ys_badge_email_notify_on" id="ys_badge_email_notify_on"',
				'value' => (bool)qa_opt('ys_badge_email_notify_on'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => qa_lang('ys_badges/badge_email_subject'),
				'tags' => 'NAME="ys_badge_email_subject" id="ys_badge_email_subject"',
				'value' => qa_opt('ys_badge_email_subject'),
				'type' => 'text',
			);

			$fields[] = array(
				'label' =>  qa_lang('ys_badges/badge_email_body'),
				'tags' => 'name="ys_badge_email_body" id="ys_badge_email_body"',
				'value' => qa_opt('ys_badge_email_body'),
				'type' => 'textarea',
				'rows' => 20,
				'note' => 'Available replacement text:<br/><br/><i>^site_title<br/>^handle<br/>^email<br/>^open<br/>^close<br/>^badge_name<br/>^post_title<br/>^post_url<br/>^profile_url<br/>^site_url<br/>^if_post_text="text"</i></td></tr></table>',
			);

			$fields[] = array(
				'type' => 'blank',
			);

		}

		return array(
			'ok' => ($ok && !isset($error)) ? $ok : null,

			'fields' => $fields,

			'buttons' => array(
				array(
					'label' => qa_lang('ys_badges/save_settings'),
					'tags' => 'NAME="ys_badge_save_settings"',
					'note' => '<br/><em>'.qa_lang('ys_badges/save_settings_desc').'</em><br/>',
				),
			),
		);
	}
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
