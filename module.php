<?php
/*
 * JustLight Theme Options Module
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2014 webtrees development team.
 * Copyright (C) 2014 JustCarmen.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

use WT\Auth;
use WT\Log;

class justlight_theme_options_WT_Module extends WT_Module implements WT_Module_Config {

	public function __construct() {
		parent::__construct();
		// Load any local user translations
		if (is_dir(WT_MODULES_DIR . $this->getName() . '/language')) {
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.mo')) {
				WT_I18N::addTranslation(
					new Zend_Translate('gettext', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.mo', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.php')) {
				WT_I18N::addTranslation(
					new Zend_Translate('array', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.php', WT_LOCALE)
				);
			}
			if (file_exists(WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.csv')) {
				WT_I18N::addTranslation(
					new Zend_Translate('csv', WT_MODULES_DIR . $this->getName() . '/language/' . WT_LOCALE . '.csv', WT_LOCALE)
				);
			}
		}
	}

	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module  */ WT_I18N::translate('JustLight Theme Options');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ WT_I18N::translate('Set options for the JustLight theme within the admin interface');
	}

	// Set default module options
	private function setDefault($key) {
		$JL_DEFAULT = array(
			'COMPACT_MENU'			 => '0',
			'COMPACT_MENU_REPORTS'	 => '1',
			'MEDIA_MENU'			 => '0',
			'MEDIA_LINK'			 => '',
			'SUBFOLDERS'			 => '1'
		);
		return $JL_DEFAULT[$key];
	}

	// Get module options
	public function options($key) {
		if ($key === 'mediafolders') {
			return $this->listMediaFolders();
		} else {
			$JL_OPTIONS = unserialize($this->getSetting('JL_OPTIONS'));
			$key = strtoupper($key);
			if (empty($JL_OPTIONS) || (is_array($JL_OPTIONS) && !array_key_exists($key, $JL_OPTIONS))) {
				return $key === 'MENU' ? $this->getDefaultMenu() : $this->setDefault($key);
			} else {
				return $key === 'MENU' ? $this->menuJustLight($JL_OPTIONS['MENU']) : $JL_OPTIONS[$key];
			}
		}
	}

	private function getDefaultMenu() {
		$menulist = array(
			'compact'	 => array(
				'title'		 => WT_I18N::translate('View'),
				'label'		 => 'compact',
				'sort'		 => '0',
				'function'	 => 'menuCompact'
			),
			'media'		 => array(
				'title'		 => WT_I18N::translate('Media'),
				'label'		 => 'media',
				'sort'		 => '0',
				'function'	 => 'menuMedia'
			),
			'homepage'	 => array(
				'title'		 => WT_I18N::translate('Home page'),
				'label'		 => 'homepage',
				'sort'		 => '1',
				'function'	 => 'menuHomePage'
			),
			'charts'	 => array(
				'title'		 => WT_I18N::translate('Charts'),
				'label'		 => 'charts',
				'sort'		 => '3',
				'function'	 => 'menuChart'
			),
			'lists'		 => array(
				'title'		 => WT_I18N::translate('Lists'),
				'label'		 => 'lists',
				'sort'		 => '4',
				'function'	 => 'menuLists'
			),
			'calendar'	 => array(
				'title'		 => WT_I18N::translate('Calendar'),
				'label'		 => 'calendar',
				'sort'		 => '5',
				'function'	 => 'menuCalendar'
			),
			'reports'	 => array(
				'title'		 => WT_I18N::translate('Reports'),
				'label'		 => 'reports',
				'sort'		 => '6',
				'function'	 => 'menuReports'
			),
			'search'	 => array(
				'title'		 => WT_I18N::translate('Search'),
				'label'		 => 'search',
				'sort'		 => '7',
				'function'	 => 'menuSearch'
			),
		);
		return $this->menuJustLight($menulist);
	}

	public function menuJustLight($menulist) {
		$modules = WT_Module::getActiveMenus();
		// add newly activated modules to the menu
		$sort = count($menulist) + 1;
		foreach ($modules as $module) {
			if ($module->getMenu() && !array_key_exists($module->getName(), $menulist)) {
				$menulist[$module->getName()] = array(
					'title'		 => $module->getTitle(),
					'label'		 => $module->getName(),
					'sort'		 => $sort++,
					'function'	 => 'menuModules'
				);
			}
		}
		// delete deactivated modules from the menu
		foreach ($menulist as $label => $menu) {
			if ($menu['function'] === 'menuModules' && !array_key_exists($label, $modules)) {
				unset($menulist[$label]);
			}
		}
		return $menulist;
	}

	private function listMenuJustLight($menulist) {
		$html = '';
		foreach ($menulist as $label => $menu) {
			$html .= '<li class="list-group-item' . $this->getStatus($label) . '">';
			foreach ($menu as $key => $val) {
				$html .= '<input type="hidden" name="NEW_JL_MENU[' . $label . '][' . $key . ']" value="' . $val . '"/>';
			}
			$html .= $menu['title'] . '</li>';
		}
		return $html;
	}

	private function listMediaFolders() {
		global $MEDIA_DIRECTORY;
		$folders = WT_Query_Media::folderList();
		foreach ($folders as $key => $value) {
			if ($key == null && empty($value)) {
				$folderlist[$MEDIA_DIRECTORY] = strtoupper(WT_I18N::translate(substr($MEDIA_DIRECTORY, 0, -1)));
			} else {
				if (count(glob(WT_DATA_DIR . $MEDIA_DIRECTORY . $value . '*')) > 0) {
					$folder = array_filter(explode("/", $value));
					// only list first level folders
					if (!empty($folder) && !array_search($folder[0], $folderlist)) {
						$folderlist[$folder[0] . '/'] = WT_I18N::translate($folder[0]);
					}
				}
			}
		}
		return $folderlist;
	}

	// Sort the array according to the $key['SORT'] input.
	private function sortArray($array, $sort_by) {
		foreach ($array as $pos => $val) {
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);

		$return_array = array();
		foreach ($tmp_array as $pos => $val) {
			$return_array[$pos]['title'] = $array[$pos]['title'];
			$return_array[$pos]['label'] = $array[$pos]['label'];
			$return_array[$pos]['sort'] = $array[$pos]['sort'];
			$return_array[$pos]['function'] = $array[$pos]['function'];
		}
		return $return_array;
	}

	// set an extra class for some menuitems
	private function getStatus($label) {
		if ($label == 'homepage') {
			$status = ' disabled';
		} elseif ($label == 'charts' || $label == 'lists' || $label == 'calendar') {
			$status = ' menu-extended';
		} elseif ($label == 'reports') {
			$status = ' menu-extended menu-reports';
		} elseif ($label == 'compact') {
			$status = ' menu-compact';
		} elseif ($label == 'media') {
			$status = ' menu-media';
		} else {
			$status = '';
		}
		return $status;
	}

	// Extend WT_Module_Config
	public function modAction($mod_action) {
		switch ($mod_action) {
			case 'admin_config':
				$this->config();
				break;
			case 'admin_reset':
				$this->jl_reset();
				$this->config();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Reset all settings to default
	private function jl_reset() {
		WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JL%'")->execute();
		Log::addConfigurationLog($this->getTitle() . ' reset to default values');
	}

	// Radio buttons
	private function radio_buttons($name, $selected) {
		$values = array(
			0	 => WT_I18N::translate('no'),
			1	 => WT_I18N::translate('yes'),
		);

		return radio_buttons($name, $values, $selected, 'class="radio-inline"');
	}

	private function config() {

		if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
			$NEW_JL_OPTIONS = WT_Filter::postArray('NEW_JL_OPTIONS');
			$NEW_JL_OPTIONS['MENU'] = $this->sortArray(WT_Filter::postArray('NEW_JL_MENU'), 'sort');

			$this->setSetting('JL_OPTIONS', serialize($NEW_JL_OPTIONS));
			Log::addConfigurationLog($this->getTitle() . ' config updated');
		}

		require WT_ROOT . 'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page;
		$controller
			->restrictAccess(Auth::isAdmin())
			->setPageTitle(WT_I18N::translate('Options for the JustLight theme'))
			->pageHeader();

		$controller->addInlineJavaScript('
			function include_css(css_file) {
				var html_doc = document.getElementsByTagName("head")[0];
				var css = document.createElement("link");
				css.setAttribute("rel", "stylesheet");
				css.setAttribute("type", "text/css");
				css.setAttribute("href", css_file);
				html_doc.appendChild(css);
			}
			include_css("' . WT_MODULES_DIR . $this->getName() . '/css/admin.css");
				
			function toggleFields(id, target) {
				var selected = jQuery(id).find("input[type=radio]:checked");
				var field = jQuery(target)
				if (selected.val() == "1") {
					field.show();
				} else {
					field.hide();
				}
				jQuery(id).on("change", "input[type=radio]", function(){
					if (jQuery(this).val() == "1") {
						field.show();
					} else {
						field.hide();
					}
				});
			}

			toggleFields("#compact-menu", "#reports");
			toggleFields("#media-menu", "#medialist, #subfolders");

			jQuery("#compact-menu").on("change", "input[type=radio]", function() {
				var reports = jQuery("#reports").find("input[type=radio]:checked");
				if (reports.val() == "1") {
					var menuExtended = jQuery(".menu-extended");
				} else {
					var menuExtended = jQuery(".menu-extended:not(.menu-reports)");
				}

				if (jQuery(this).val() == "1") {
					jQuery(".menu-compact").insertAfter(jQuery(".menu-extended:last"));
					jQuery(menuExtended).appendTo(jQuery("#trash-menu"));
				} else {
					jQuery(menuExtended).insertAfter(jQuery(".menu-compact"));
					jQuery(".menu-compact").appendTo(jQuery("#trash-menu"));
				}
				jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
			});

			jQuery("#reports").on("change", "input[type=radio]", function() {
				if (jQuery(this).val() == "1") {
					jQuery(".menu-reports").appendTo(jQuery("#trash-menu"));
				} else {
					jQuery(".menu-reports").insertAfter(jQuery(".menu-compact"));
				}
				jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
			});

			jQuery("#media-menu").on("change", "input[type=radio]", function() {
				if (jQuery(this).val() == "1") {
					jQuery(".menu-media").appendTo(jQuery("#sort-menu"));
				} else {
					jQuery(".menu-media").appendTo(jQuery("#trash-menu"));
				}
				jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
			});

			jQuery("#medialist select").each(function() {
				if(jQuery(this).val() == "' . $this->options('media_link') . '") {
					jQuery(this).prop("selected", true);
				}
			});

			 jQuery("#sort-menu").sortable({
				items: "li:not(.disabled)",
				cursor: "move",
				update: function(event, ui) {
					jQuery("#sort-menu, #trash-menu").trigger("sortupdate")
				}
			});
			jQuery("#sort-menu li, #trash-menu li").not(".disabled").css("cursor", "move");

			//-- update the order numbers after drag-n-drop sorting is complete
			jQuery("#sort-menu").bind("sortupdate", function(event, ui) {
				jQuery("#"+jQuery(this).attr("id")+" input[name*=sort]").each(
					function (index, element) {
						element.value = index + 1;
					}
				);
				jQuery("#trash-menu input[name*=sort]").attr("value", "0");
			});
		');
		?>

		<!-- ADMIN PAGE CONTENT -->
		<ol class="breadcrumb small">
			<li><a href="admin.php"><?php echo WT_I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo WT_I18N::translate('Module administration'); ?></a></li>
			<li class="active"><?php echo $this->getTitle(); ?></li>
		</ol>
		<h2><?php echo $this->getTitle(); ?></h2>
		<form action="<?php echo $this->getConfigLink(); ?>" enctype="multipart/form-data" name="configform" method="post" class="form-horizontal">
			<input type="hidden" value="1" name="save">
			<?php echo WT_Filter::getCsrf(); ?>
			<div id="accordion" class="panel-group">
				<div id="panel1" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a href="#collapseOne" data-target="#collapseOne" data-toggle="collapse"><?php echo WT_I18N::translate('Options'); ?></a>
						</h4>
					</div>
					<div class="panel-collapse collapse in" id="collapseOne">
						<div class="panel-body">
							<!-- COMPACT MENU -->
							<div id="compact-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo WT_I18N::translate('Use a compact menu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radio_buttons('NEW_JL_OPTIONS[COMPACT_MENU]', $this->options('compact_menu')); ?>
									<p class="small text-muted"><?php echo WT_I18N::translate('In the compact “View”-menu the menus for Charts, Lists, Calendar and (optionally) Reports will be merged together.'); ?></p>
								</div>
							</div>
							<!-- REPORTS -->
							<div id="reports" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo WT_I18N::translate('Include the reports topmenu in the compact \'View\' topmenu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radio_buttons('NEW_JL_OPTIONS[COMPACT_MENU_REPORTS]', $this->options('compact_menu_reports')); ?>
								</div>
							</div>
							<!-- MEDIA MENU -->
							<div id="media-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo WT_I18N::translate('Media menu in topmenu'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radio_buttons('NEW_JL_OPTIONS[MEDIA_MENU]', $this->options('media_menu')); ?>
									<p class="small text-muted"><?php echo WT_I18N::translate('If this option is set the media menu will be moved to the topmenu. The names of first level media folders from your media folder on the server will be used as submenu items of the new media menu. Warning: these submenu items are not translated automatically. Use a custom language file to translate your menu items. Read the webrees WIKI for more information.'); ?></p>
								</div>
							</div>
							<!-- MEDIA FOLDER LIST -->
							<div id="medialist" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo WT_I18N::translate('Choose a folder as default for the main menu link'); ?>
								</label>
								<div class="col-sm-2">
									<?php echo select_edit_control('NEW_JL_OPTIONS[MEDIA_LINK]', $this->options('mediafolders'), null, $this->options('media_link'), 'class="form-control"'); ?>
								</div>
								<div class="col-sm-8"><p class="small text-muted"><?php echo WT_I18N::translate('The media folder you choose here will be used as default folder for media menu link of the main menu. If you click on the media link or icon in the main menu, the page opens with the media items from this folder.'); ?></p></div>
							</div>
							<!-- SUBFOLDERS -->
							<div id="subfolders" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo WT_I18N::translate('Include subfolders'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo $this->radio_buttons('NEW_JL_OPTIONS[SUBFOLDERS]', $this->options('subfolders')); ?>
									<p class="small text-muted"><?php echo WT_I18N::translate('If you set this option the results on the media list page will include subfolders.'); ?></p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="panel2" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a class="collapsed" href="#collapseTwo" data-target="#collapseTwo" data-toggle="collapse">
								<?php echo WT_I18N::translate('Sort Topmenu items'); ?>
							</a>
						</h4>
					</div>
					<div class="panel-collapse collapse" id="collapseTwo">
						<div class="panel-heading">
							<?php echo WT_I18N::translate('Click a row, then drag-and-drop to re-order the topmenu items. Then click the “save” button.'); ?>
						</div>
						<div class="panel-body">
							<?php
							$menulist = $this->options('menu');
							foreach ($menulist as $label => $menu) {
								$menu['sort'] == 0 ? $trashMenu[$label] = $menu : $activeMenu[$label] = $menu;
							}
							?>
							<?php if (isset($activeMenu)): ?>
								<ul id="sort-menu" class="list-group"><?php echo $this->listMenuJustLight($activeMenu); ?></ul>
							<?php endif; ?>
							<?php if (isset($trashMenu)): // trashcan for toggling the compact menu. ?>
								<ul id="trash-menu" class="sr-only"><?php echo $this->listMenuJustLight($trashMenu); ?></ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<button class="btn btn-primary" type="submit"><?php echo WT_I18N::translate('Save'); ?></button>
			<button class="btn btn-primary" type="reset" onclick="if (confirm('<?php echo WT_I18N::translate('The settings will be reset to default. Are you sure you want to do this?'); ?>'))
								window.location.href = 'module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_reset';">
					<?php echo WT_I18N::translate('Reset'); ?>
			</button>
		</form>
		<?php
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

}
