<?php

	class qa_ys_badge_page {

		var $directory;
		var $urltoroot;

		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}

		function suggest_requests() // for display in admin interface
		{
			return array(
				array(
					'title' => qa_lang('ys_badges/badges'),
					'request' => 'badges',
					'nav' => 'M', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
				),
			);
		}

		function match_request($request)
		{
			if ($request === 'badges') {
				return true;
			}

			return false;
		}

		function process_request($request)
		{
			$qa_content = qa_content_prepare();

			$qa_content['title'] = qa_lang('ys_badges/badge_list_title');

			$badges = ys_get_badge_list();

			$totalawarded = 0;

			$qa_content['custom'] = '<em>'.qa_lang('ys_badges/badge_list_pre').'</em><br />';
			$qa_content['custom2'] = '<table cellspacing="20">';
			$c = 2;

			$result = ys_badge_db::get_all_badges();

			$count = array();

			foreach($result as $r) {
				if(qa_opt('ys_badge_'.$r['badge_slug'].'_enabled') == '0') continue;
				if(isset($count[$r['badge_slug']][$r['user_id']])) $count[$r['badge_slug']][$r['user_id']]++;
				else $count[$r['badge_slug']][$r['user_id']] = 1;
				$totalawarded++;
				if(isset($count[$r['badge_slug']]['count'])) $count[$r['badge_slug']]['count']++;
				else $count[$r['badge_slug']]['count'] = 1;
			}


			foreach($badges as $slug => $info) {
				if(qa_opt('ys_badge_'.$slug.'_enabled') == '0') continue;
				$badge_name = ys_badge_name($slug);
				if(!qa_opt('ys_badge_'.$slug.'_name')) qa_opt('badge_'.$slug.'_name',$badge_name);
				$name = qa_opt('ys_badge_'.$slug.'_name');
				$var = qa_opt('ys_badge_'.$slug.'_var');
				$desc = ys_badge_desc_replace($slug,$var,false);
				$type = ys_get_badge_level($info['level']);
				$types = $type['slug'];
				$typen = $type['name'];
				$qa_content['custom'.++$c]='<tr><td class="ys-badge-entry"><div class="ys-badge-entry-badge"><span class="ys-badge-'.$types.'" title="'.$typen.'">'.$name.'</span>&nbsp;<span class="ys-badge-entry-desc">'.$desc.'</span>'.(isset($count[$slug])?'&nbsp;<span title="'.$count[$slug]['count'].' '.qa_lang('ys_badges/awarded').'" class="ys-badge-count-link" onclick="jQuery(\'#ys-badge-users-'.$slug.'\').slideToggle()">x'.$count[$slug]['count'].'</span>':'').'</div>';

				// source users

				if(qa_opt('ys_badge_show_source_users') && isset($count[$slug])) {

					$users = array();

					require_once QA_INCLUDE_DIR.'qa-app-users.php';

					$qa_content['custom'.$c] .='<div style="display:none" id="ys-badge-users-'.$slug.'" class="ys-badge-users">';
					foreach($count[$slug] as $uid => $ucount) {
						if($uid == 'count') continue;
						$handle = qa_userid_to_handle($uid);

						if(!$handle) continue;

						$users[] = '<a href="'.qa_path_html('user/'.$handle).'">'.$handle.($ucount>1?' x'.$ucount:'').'</a>';
					}
					$qa_content['custom'.$c] .= implode(', ',$users).'</div>';
				}
				$qa_content['custom'.$c] .= '</td></tr>';
			}


			$qa_content['custom'.++$c]='<tr><td class="badge-entry"><span class="ys-total-badges">'.qa_lang('ys_badges/badges_total').count($badges).'</span>'.($totalawarded > 0 ? ', <span class="ys_total-badge-count">'.qa_lang('ys_badges/awarded_total').$totalawarded.'</span>':'').'</td></tr></table>';

			if(isset($qa_content['navigation']['main']['custom-2'])) $qa_content['navigation']['main']['custom-2']['selected'] = true;

			return $qa_content;
		}

	};


/*
	Omit PHP closing tag to help avoid accidental output
*/
