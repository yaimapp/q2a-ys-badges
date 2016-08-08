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

				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS ^ys_userbadges ('.
						'id INT(11) NOT NULL AUTO_INCREMENT,'.
						'awarded_at DATETIME NOT NULL,'.
						'user_id INT(11) NOT NULL,'.
						'notify TINYINT DEFAULT 0 NOT NULL,'.
						'object_id INT(10),'.
						'badge_slug VARCHAR (64) CHARACTER SET ascii DEFAULT \'\','.
						'PRIMARY KEY (id)'.
					') ENGINE=InnoDB DEFAULT CHARSET=utf8'
				);

				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS ^ys_achievements ('.
						'user_id INT(11) UNIQUE NOT NULL,'.
						'first_visit DATETIME,'.
						'oldest_consec_visit DATETIME,'.
						'longest_consec_visit INT(10),'.
						'last_visit DATETIME,'.
						'total_days_visited INT(10),'.
						'questions_read INT(10),'.
						'posts_edited INT(10)'.
					') ENGINE=InnoDB DEFAULT CHARSET=utf8'
				);

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
				'label' => 'Ys Badge css stylesheet',
				'tags' => 'NAME="ys_badges_css"',
				'value' => qa_opt('ys_badges_css'),
				'rows' => 20,
				'type' => 'textarea',
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
