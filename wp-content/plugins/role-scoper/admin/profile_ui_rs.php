<?php
if( basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME']) )
	die();

require_once( dirname(__FILE__).'/admin_ui_lib_rs.php' );

class ScoperProfileUI {

	public static function display_ui_user_roles($user, $groups_only = false) {
		global $scoper;

		$blog_roles = array();
		$term_roles = array();

		$blog_roles = $user->get_blog_roles_daterange( 'rs', array( 'include_role_duration_key' => true, 'enforce_duration_limits' => false ) );	// arg: return array with additional key dimension for role duration 
		
		// for Administrators, display any custom post General Roles which were auto-assigned to maintain default editing rights
		global $current_rs_user;
		if ( $current_rs_user->ID == $user->ID ) {
			if ( is_content_administrator_rs() )
				$blog_roles[''][''] = ( isset($blog_roles['']['']) ) ? array_merge( $current_rs_user->assigned_blog_roles[''] ) : $current_rs_user->assigned_blog_roles[''];
		}
		
		foreach ( $scoper->taxonomies->get_all() as $taxonomy => $tx )	
			$term_roles[$taxonomy] = $user->get_term_roles_daterange( $taxonomy, 'rs', array( 'include_role_duration_key' => true, 'enforce_duration_limits' => false ) );	// arg: return array with additional key dimension for role duration

		$duration_limits_enabled = scoper_get_option( 'role_duration_limits' );
		$content_date_limits_enabled = scoper_get_option( 'role_content_date_limits' );
		
		$html = '';
		
		if ( $groups_only ) {
			if ( IS_MU_RS && scoper_get_option( 'mu_sitewide_groups', true ) ) {
				global $blog_id;
				
				$list = scoper_get_blog_list( 0, 'all' );
				
				$blog_path = '';
				foreach ( $list as $blog ) {
					if ( $blog['blog_id'] == $blog_id ) {
						$blog_path = $blog['path'];
						break;	
					}
				}

				$group_caption = sprintf( __('Group Roles %1$s(for %2$s)%3$s', 'scoper'), '<span style="font-weight: normal">', rtrim($blog_path, '/'), '</span>' );
			} else
				$group_caption = __('Group Roles', 'scoper');

		} else {
			$html .= "<div id='userprofile_rolesdiv_rs' class='rs-scoped_role_profile'>";
			$html .= "<h3>" . __('Scoped Roles', 'scoper') . "</h3>";

			$wp_blog_roles = array_intersect_key( $user->assigned_blog_roles[''], $scoper->role_defs->get_matching( 'wp' ) );

			if ( ! empty($wp_blog_roles) ) {
				$display_names = array();
				
				foreach (array_keys($wp_blog_roles) as $role_handle)
					$display_names []= $scoper->role_defs->get_display_name($role_handle);

				$html .= sprintf( __("<strong>Assigned WordPress Role:</strong> %s", 'scoper'), implode(", ", $display_names) );
			
				if ( $contained_roles = $scoper->role_defs->get_contained_roles( array_keys($wp_blog_roles), false, 'rs' ) ) {
					$display_names = array();			
					foreach (array_keys($contained_roles) as $role_handle)
						$display_names []= $scoper->role_defs->get_display_name($role_handle);
					
					$html .= '<br /><span class="rs-gray">';
					$html .= sprintf( __("(contains %s)", 'scoper'), implode(", ", $display_names) );
					$html .= '</span>';
				}
			}
			
			$html .= '<br /><br />';
		}
		
		
		$display_names = array();
		
		foreach ( array_keys($blog_roles) as $duration_key ) {
			if ( is_serialized($duration_key) ) {
				$role_date_limits = unserialize( $duration_key );
				$role_date_limits->date_limited = true;
			} else
				$role_date_limits = array();

			foreach ( array_keys($blog_roles[$duration_key]) as $date_key ) {
				$display_names = array();
				
				if ( is_serialized($date_key) ) {
					$content_date_limits = unserialize( $date_key );
					$content_date_limits->content_date_limited = true;
				} else
					$content_date_limits = array();

				$date_caption = '';
					
				if ( $role_date_limits || $content_date_limits ) {
					$limit_class = ''; // unused byref arg
					$limit_style = ''; // unused byref arg
					$link_class = '';  // unused byref arg
					ScoperAdminUI::set_agent_formatting( array_merge( (array) $role_date_limits, (array) $content_date_limits ), $date_caption, $limit_class, $link_class, $limit_style, false ); // arg: no title='' wrapper around date_caption
					$date_caption = '<span class="rs-gray"> ' . trim($date_caption) . '</span>';
				}
					
				if ( $rs_blog_roles = $scoper->role_defs->filter( $blog_roles[$duration_key][$date_key], array( 'role_type' => 'rs' ) ) ) {
					foreach ( array_keys($rs_blog_roles) as $role_handle )
						$display_names []= $scoper->role_defs->get_display_name($role_handle);
					
					$url = "admin.php?page=rs-general_roles";
					$linkopen = "<strong><a href='$url'>";
					$linkclose = "</a></strong>";
					$list = implode(", ", $display_names);
					
					if ( $groups_only )
						$html .= sprintf( _n('<strong>%1$sGeneral Role%2$s</strong>%4$s: %3$s', '<strong>%1$sGeneral Roles%2$s</strong>%4$s: %3$s', count($display_names), 'scoper'), $linkopen, $linkclose, $list, $date_caption);
					else
						$html .= sprintf( _n('<strong>Additional %1$sGeneral Role%2$s</strong>%4$s: %3$s', '<strong>Additional %1$sGeneral Roles%2$s</strong>%4$s: %3$s', count($display_names), 'scoper'), $linkopen, $linkclose, $list, $date_caption);
				
					if ( $contained_roles = $scoper->role_defs->get_contained_roles( array_keys($rs_blog_roles), false, 'rs' ) ) {
						$display_names = array();
						foreach (array_keys($contained_roles) as $role_handle)
							$display_names []= $scoper->role_defs->get_display_name($role_handle);
						
						$html .= '<br /><span class="rs-gray">';
						$html .= sprintf( __("(contains %s)", 'scoper'), implode(", ", $display_names) );
						$html .= '</span>';
					}
					
					$html .= '<br /><br />';
				}
			} // end foreach content date range
		} // end foreach role duration date range
	
		
		$disable_role_admin = false;
		
		global $profileuser;

		$viewing_own_profile = ( ! empty($profileuser) && ( $profileuser->ID == $current_rs_user->ID ) );
		
		if ( ! $viewing_own_profile ) {
			if ( $require_blogwide_editor = scoper_get_option('role_admin_blogwide_editor_only') ) {
				if ( ( 'admin' == $require_blogwide_editor ) && ! is_user_administrator_rs() )
					return false;
					
				if ( ( 'admin_content' == $require_blogwide_editor ) && ! is_content_administrator_rs() )
					return false;
		
				$disable_role_admin = ! $scoper->user_can_edit_blogwide( 'post', '', array( 'require_others_cap' => true, 'status' => 'publish' ) );
			}
		}

		foreach ( $scoper->taxonomies->get_all() as $taxonomy => $tx ) {
			if ( empty($term_roles[$taxonomy]) )
				continue;
			
			$val = ORDERBY_HIERARCHY_RS;
			$args = array( 'order_by' => $val );
			if ( ! $terms = $scoper->get_terms($taxonomy, UNFILTERED_RS, COLS_ALL_RS, 0, $args) )
				continue;

			$object_types = array();
			
			$obj_src = $scoper->data_sources->get( $tx->object_source );
			
			if ( ! $obj_src || ! is_array($obj_src->object_types) )
				continue;

			foreach ( array_keys($obj_src->object_types) as $object_type)
				if ( scoper_get_otype_option('use_term_roles', $tx->object_source, $object_type) )
					$object_types []= $object_type;
				
			if ( ! $object_types )
				continue;

			$object_types []= $taxonomy;
				
			$admin_terms = ( $disable_role_admin ) ? array() : $scoper->get_terms($taxonomy, ADMIN_TERMS_FILTER_RS, COL_ID_RS);

			$strict_terms = $scoper->get_restrictions(TERM_SCOPE_RS, $taxonomy);

			$role_defs = $scoper->role_defs->get_matching('rs', $tx->object_source, $object_types);

			$tx_src = $scoper->data_sources->get( $tx->source );
			$col_id = $tx_src->cols->id;
			$col_name = $tx_src->cols->name;
			
			$term_names = array();

			foreach ( $terms as $term )
				$term_names[$term->$col_id] = $term->$col_name;
				
			foreach ( array_keys($term_roles[$taxonomy]) as $duration_key ) {
				if ( is_serialized($duration_key) ) {
					$role_date_limits = unserialize( $duration_key );
					$role_date_limits->date_limited = true;
				} else
					$role_date_limits = array();
					
				foreach ( array_keys($term_roles[$taxonomy][$duration_key]) as $date_key ) {
					if ( is_serialized($date_key) ) {
						$content_date_limits = unserialize( $date_key );
						$content_date_limits->content_date_limited = true;
					} else
						$content_date_limits = array();
						
					$title = '';
					$date_caption = '';
					$limit_class = '';
					$limit_style = '';
					$link_class = '';
					$style = '';
					
					if ( $role_date_limits || $content_date_limits ) {
						ScoperAdminUI::set_agent_formatting( array_merge( (array) $role_date_limits, (array) $content_date_limits ), $date_caption, $limit_class, $link_class, $limit_style );
						$title = "title='$date_caption'";
						$date_caption = '<span class="rs-gray"> ' . trim($date_caption) . '</span>';
					}
						
					if ( $admin_terms ) {
						$url = "admin.php?page=rs-$taxonomy-roles_t";
						//$html .= ("\n<h4><a href='$url'>" . sprintf(_ x('%1$s Roles%2$s:', 'Category Roles, content date range', 'scoper'), $tx->display_name, '</a><span style="font-weight:normal">' . $date_caption) . '</span></h4>' );
						$html .= ("\n<h4><a href='$url'>" . sprintf(__('%1$s Roles%2$s:', 'scoper'), $tx->labels->singular_name, '</a><span style="font-weight:normal">' . $date_caption) . '</span></h4>' );
					} else
						$html .= ("\n<h4>" . sprintf(__('%1$s Roles%2$s:', 'scoper'), $tx->labels->singular_name, $date_caption) . '</h4>' );
						//$html .= ("\n<h4>" . sprintf(_ x('%1$s Roles%2$s:', 'Category Roles, content date range', 'scoper'), $tx->display_name, $date_caption) . '</h4>' );
		
					$html .= '<ul class="rs-termlist" style="padding-left:0.1em;">';
					$html .= '<li>';
					$html .= '<table class="widefat"><thead><tr class="thead">';
					$html .= '<th class="rs-tightcol">' . __awp('Role') . '</th>';
					$html .= '<th>' . $tx->labels->name . '</th>';
					$html .= '</tr></thead><tbody>';

					
					foreach ( array_keys($role_defs) as $role_handle ) {
						if ( isset( $term_roles[$taxonomy][$duration_key][$date_key][$role_handle] ) ) {
		
							$role_terms = $term_roles[$taxonomy][$duration_key][$date_key][$role_handle];
							$role_display = $scoper->role_defs->get_display_name($role_handle);

							$term_role_list = array();
							foreach ( $role_terms as $term_id ) {
								if ( ! in_array( $term_id, $admin_terms ) )
									$term_role_list []= $term_names[$term_id];
								elseif ( isset($strict_terms['restrictions'][$role_handle][$term_id]) 
								|| ( isset($strict_terms['unrestrictions'][$role_handle]) && is_array($strict_terms['unrestrictions'][$role_handle]) && ! isset($strict_terms['unrestrictions'][$role_handle][$term_id]) ) )
									$term_role_list []= "<span class='rs-backylw'><a {$title}{$limit_style}class='{$link_class}{$limit_class}' href='$url#item-$term_id'>" . $term_names[$term_id] . '</a></span>';
								else
									$term_role_list []= "<a {$title}{$limit_style}class='{$link_class}{$limit_class}' href='$url#item-$term_id'>" . $term_names[$term_id] . '</a>';
							}
							
							$html .= "\r\n"
								. "<tr$style>"
								. "<td>" . str_replace(' ', '&nbsp;', $role_display) . "</td>"
								. '<td>' . implode(', ', $term_role_list) . '</td>'
								. "</tr>";
							$style = ( ' class="alternate"' == $style ) ? ' class="rs-backwhite"' : ' class="alternate"';
						}
					}
		
					$html .= '</tbody></table>';
					$html .= '</li></ul><br />';
					
				} // end foreach content date range
			
			} // end foreach role duration date range	
				
		} // end foreach taxonomy
		
		require_once( dirname(__FILE__).'/object_roles_list.php');
		$html .= scoper_object_roles_list($user, array( 'enforce_duration_limits' => false, 'is_user_profile' => $viewing_own_profile, 'echo' => false ) );
		
		if ( $groups_only ) {
			//if ( empty($rs_blog_roles) && empty($term_role_list) && empty($got_obj_roles) )
			if ( $html ) {
				echo '<div>';
				echo "<h3>$group_caption</h3>";
				echo $html;
				echo '</div>';
				
				if ( IS_MU_RS )
					echo '<br /><hr /><br />';
			}
				//echo '<p>' . __('No roles are assigned to this group.', 'scoper'), '</p>';
		} else {
			echo $html;
			echo '</div>';
		}
		
	} // end function ui_user_roles

	
	
	public static function display_ui_group_roles($group_id) {
			
		$users = ScoperAdminLib::get_group_members($group_id, COL_ID_RS);
			
		$args = array('disable_user_roles' => true, 'filter_usergroups' => array($group_id => true), 'disable_wp_roles' => true );
		
		$user_id = ( $users ) ? $users[0] : 0;
		$user = new WP_Scoped_User($user_id, '', $args);

		if ( ! $users )
			$user->groups = array( $group_id => true );
	
		if ( $group = ScoperAdminLib::get_group($group_id) ) {
			if ( ! strpos( $group->meta_id, '_nr_' ) ) {
				global $wpdb;
				
				echo '<div class="rs-group-profile">';
				
				if ( IS_MU_RS && scoper_get_option( 'mu_sitewide_groups' ) ) {
					global $blog_id;
					$blog_ids = scoper_get_col( "SELECT blog_id FROM $wpdb->blogs ORDER BY blog_id" );
					$orig_blog_id = $blog_id;	
				} else
					$blog_ids = array( '1' );

				foreach ( $blog_ids as $id ) {
					if ( count($blog_ids) > 1 )
						switch_to_blog( $id );

					if ( ! $wpdb->get_results( "SHOW TABLES LIKE '$wpdb->user2role2object_rs'" ) )
						continue;
					
					ScoperProfileUI::display_ui_user_roles($user, true);  //arg: groups only
				}
				
				echo '</div>';
				
				if ( count($blog_ids) > 1 )
					switch_to_blog( $orig_blog_id );
			}
		}
	}
	
	
	public static function display_ui_user_groups() {
		if ( ! $all_groups = ScoperAdminLib::get_all_groups(UNFILTERED_RS) )
			return;

		global $current_rs_user, $profileuser;
		$user_id = $profileuser->ID;
		
		$editable_ids = ScoperAdminLib::get_all_groups(FILTERED_RS, COL_ID_RS);
		
		if ( $user_id == $current_rs_user->ID )
			$stored_groups = array_keys($current_rs_user->groups);
		else {
			$user = new WP_Scoped_User($user_id, '', array( 'skip_role_merge' => 1 ) );
			$stored_groups = array_keys($user->groups);
		}
		
		// can't manually edit membership of WP Roles groups, other metagroups
		$all_ids = array();
		foreach ( $all_groups as $key => $group ) {
			$all_ids[]= $group->ID;
			
			if ( ! empty($group->meta_id) && ! is_null($group->meta_id) && in_array( $group->ID, $editable_ids ) && ! strpos($group->meta_id, '_editable') ) {
				$editable_ids = array_diff( $editable_ids, array($group->ID) );
				$stored_groups = array_diff( $stored_groups, array($group->ID) );
				unset( $all_groups[$key] );
			}
		}
		
		// avoid incorrect eligible count if orphaned group roles are included in editable_ids
		$editable_ids = array_intersect( $editable_ids, $all_ids );
		
		if ( ! $editable_ids && ! $stored_groups )
			return;
			
		echo "<div id='userprofile_groupsdiv_rs' class='rs-group_members'>";
		echo "<h3>";
		
		if ( defined( 'GROUPS_CAPTION_RS' ) )
			echo ( GROUPS_CAPTION_RS );
		else
			_e( 'User Groups', 'scoper' );
		 
		echo "</h3>";
		
		if ( scoper_get_option( 'group_ajax' ) ) {
			$arr_display_names = array();

			$group_ids = array();
			$group_ids['active'] = $stored_groups;
			$group_ids['recommended'] = $current_rs_user->get_groups_for_user( $user_id, array( 'status' => 'recommended' ) );
			$group_ids['requested'] = $current_rs_user->get_groups_for_user( $user_id, array( 'status' => 'requested' ) );

			foreach ( $group_ids as $key => $ids ) {
				foreach ( $ids as $group_id ) {
					foreach ( array_keys($all_groups) as $nkey ) {
						if ( $all_groups[$nkey]->ID == $group_id ) {
							$arr_display_names [$key][$group_id]= $all_groups[$nkey]->display_name;
							break;	 
						}
					}
				}
			}
			
			global $scoper_user_search;
			$scoper_user_search->output_html( $arr_display_names, 'groups' );
		} else {
			$css_id = 'group';
			
			$locked_ids = array_diff($stored_groups, $editable_ids );
			$args = array( 'suppress_extra_prefix' => true, 'eligible_ids' => $editable_ids, 'locked_ids' => $locked_ids );
			
			require_once( dirname(__FILE__).'/agents_checklist_rs.php');
	 		ScoperAgentsChecklist::agents_checklist( ROLE_BASIS_GROUPS, $all_groups, $css_id, array_flip($stored_groups), $args);
			
			echo '</fieldset>';
		}

		echo '</div><br />';
		
		echo "<input type='hidden' name='rs_editing_user_groups' value='1' />";
	}
	
} // end class ScoperProfileUI