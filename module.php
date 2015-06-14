<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * Copyright (C) 2015 JustCarmen
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Fisharebest\Webtrees;

use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Query\QueryMedia;

class JustLightThemeOptionsModule extends AbstractModule implements ModuleConfigInterface {

	public function __construct() {
		parent::__construct('justlight_theme_options');

		// update the database if neccessary
		self::updateSchema();
	}

	// Extend Module
	public function getTitle() {
		return /* I18N: Name of a module  */ I18N::translate('JustLight Theme Options');
	}

	// Extend Module
	public function getDescription() {
		return /* I18N: Description of the module */ I18N::translate('Set options for the JustLight theme within the admin interface');
	}

	// Set default module options
	private function setDefault($key) {
		$JL_DEFAULT = array(
			'TITLESIZE'				 => '32',
			'COMPACT_MENU'			 => '0',
			'COMPACT_MENU_REPORTS'	 => '1',
			'MEDIA_MENU'			 => '0',
			'MEDIA_LINK'			 => '',
			'SHOW_SUBFOLDERS'		 => '1'
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
				'title'		 => I18N::translate('View'),
				'label'		 => 'compact',
				'sort'		 => '0',
				'function'	 => 'menuCompact'
			),
			'media'		 => array(
				'title'		 => I18N::translate('Media'),
				'label'		 => 'media',
				'sort'		 => '0',
				'function'	 => 'menuMedia'
			),
			'homepage'	 => array(
				'title'		 => I18N::translate('Home page'),
				'label'		 => 'homepage',
				'sort'		 => '1',
				'function'	 => 'menuHomePage'
			),
			'charts'	 => array(
				'title'		 => I18N::translate('Charts'),
				'label'		 => 'charts',
				'sort'		 => '3',
				'function'	 => 'menuChart'
			),
			'lists'		 => array(
				'title'		 => I18N::translate('Lists'),
				'label'		 => 'lists',
				'sort'		 => '4',
				'function'	 => 'menuLists'
			),
			'calendar'	 => array(
				'title'		 => I18N::translate('Calendar'),
				'label'		 => 'calendar',
				'sort'		 => '5',
				'function'	 => 'menuCalendar'
			),
			'reports'	 => array(
				'title'		 => I18N::translate('Reports'),
				'label'		 => 'reports',
				'sort'		 => '6',
				'function'	 => 'menuReports'
			),
			'search'	 => array(
				'title'		 => I18N::translate('Search'),
				'label'		 => 'search',
				'sort'		 => '7',
				'function'	 => 'menuSearch'
			),
		);
		return $this->menuJustLight($menulist);
	}

	public function menuJustLight($menulist) {
		$modules = array();
		foreach (Tree::getAll() as $tree) {
			$modules = array_merge(Module::getActiveMenus($tree), $modules);
		}

		// add newly activated modules to the menu
		$sort = count($menulist) + 1;
		foreach ($modules as $module_name => $module) {
			if ($module->getMenu() && !array_key_exists($module_name, $menulist)) {
				$menulist[$module_name] = array(
					'title'		 => $module->getTitle(),
					'label'		 => $module_name,
					'sort'		 => $sort++,
					'function'	 => 'menuModule'
				);
			}
		}
		// delete deactivated modules from the menu
		foreach ($menulist as $label => $menu) {
			if ($menu['function'] === 'menuModule' && !array_key_exists($label, $modules)) {
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
		global $WT_TREE;

		$MEDIA_DIRECTORY = $WT_TREE->getPreference('MEDIA_DIRECTORY');
		$folders = QueryMedia::folderList();

		foreach ($folders as $key => $value) {
			if ($key == null && empty($value)) {
				$folderlist[$MEDIA_DIRECTORY] = strtoupper(I18N::translate(substr($MEDIA_DIRECTORY, 0, -1)));
			} else {
				if (count(glob(WT_DATA_DIR . $MEDIA_DIRECTORY . $value . '*')) > 0) {
					$folder = array_filter(explode("/", $value));
					// only list first level folders
					if (!empty($folder) && !array_search($folder[0], $folderlist)) {
						$folderlist[$folder[0] . '/'] = I18N::translate($folder[0]);
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

	// Extend ModuleConfigInterface
	public function modAction($mod_action) {
		switch ($mod_action) {
			case 'admin_config':
				$this->config();
				break;
			case 'admin_reset':
				$this->resetAll();
				$this->config();
				break;
			default:
				http_response_code(404);
				break;
		}
	}

	// Reset all settings to default
	private function resetAll() {
		Database::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JL%'")->execute();
		Log::addConfigurationLog($this->getTitle() . ' reset to default values');
	}

	private function config() {

		if (Filter::postBool('save') && Filter::checkCsrf()) {
			$NEW_JL_OPTIONS = Filter::postArray('NEW_JL_OPTIONS');
			$NEW_JL_OPTIONS['MENU'] = $this->sortArray(Filter::postArray('NEW_JL_MENU'), 'sort');

			$this->setSetting('JL_OPTIONS', serialize($NEW_JL_OPTIONS));
			Log::addConfigurationLog($this->getTitle() . ' config updated');
		}

		$controller = new PageController;
		$controller
			->restrictAccess(Auth::isAdmin())
			->setPageTitle(I18N::translate('Options for the JustLight theme'))
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
			toggleFields("#media-menu", "#subfolders");

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
			<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li class="active"><?php echo $this->getTitle(); ?></li>
		</ol>
		<h2><?php echo $this->getTitle(); ?></h2>
		<form action="<?php echo $this->getConfigLink(); ?>" enctype="multipart/form-data" name="configform" method="post" class="form-horizontal">
			<input type="hidden" value="1" name="save">
			<?php echo Filter::getCsrf(); ?>
			<div id="accordion" class="panel-group">
				<div id="panel1" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a href="#collapseOne" data-target="#collapseOne" data-toggle="collapse"><?php echo I18N::translate('Options'); ?></a>
						</h4>
					</div>
					<div class="panel-collapse collapse in" id="collapseOne">
						<div class="panel-body">
							<!-- TREE TITLE SIZE -->
							<div id="title-size" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Size of the Family tree title'); ?>
								</label>
								<div class="col-sm-2">
									<div class="input-group">
										<input
											type="text"
											value="<?php echo $this->options('titlesize'); ?>"
											size="2"
											name="NEW_JL_OPTIONS[TITLESIZE]"
											class="form-control"
											>
										<span class="input-group-addon">px</span>
									</div>
								</div>
							</div>
							<!-- COMPACT MENU -->
							<div id="compact-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Use a compact menu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JL_OPTIONS[COMPACT_MENU]', $this->options('compact_menu'), 'class="radio-inline"'); ?>
									<p class="small text-muted"><?php echo I18N::translate('In the compact “View”-menu the menus for Charts, Lists, Calendar and (optionally) Reports will be merged together.'); ?></p>
								</div>
							</div>
							<!-- REPORTS -->
							<div id="reports" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Include the reports topmenu in the compact \'View\' topmenu?'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JL_OPTIONS[COMPACT_MENU_REPORTS]', $this->options('compact_menu_reports'), 'class="radio-inline"'); ?>
								</div>
							</div>
							<!-- MEDIA MENU -->
							<?php $folders = $this->options('mediafolders'); ?>
							<div id="media-menu" class="form-group form-group-sm">
								<label class="control-label col-sm-4">
									<?php echo I18N::translate('Media menu in topmenu'); ?>
								</label>
								<div class="col-sm-8">
									<?php echo FunctionsEdit::editFieldYesNo('NEW_JL_OPTIONS[MEDIA_MENU]', $this->options('media_menu'), 'class="radio-inline"'); ?>
									<p class="small text-muted"><?php echo I18N::translate('If this option is set the media menu will be moved to the topmenu.'); ?></p>
									<?php if (count($folders) > 1): // add extra information about subfolders  ?>
										<p class="small text-muted"><?php echo I18N::translate('The names of first level media folders from your media folder on the server will be used as submenu items of the new media menu. Warning: these submenu items are not translated automatically. Use a custom language file to translate your menu items. Read the webrees WIKI for more information.'); ?></p>
									<?php endif; ?>
								</div>
							</div>
							<?php if (count($folders) > 1): // only show this option if we have subfolders  ?>
								<!-- SHOW SUBFOLDERS -->
								<div id="subfolders" class="form-group form-group-sm">
									<label class="control-label col-sm-4">
										<?php echo I18N::translate('Include subfolders'); ?>
									</label>
									<div class="col-sm-8">
										<?php echo FunctionsEdit::editFieldYesNo('NEW_JL_OPTIONS[SHOW_SUBFOLDERS]', $this->options('show_subfolders'), 'class="radio-inline"'); ?>
										<p class="small text-muted"><?php echo I18N::translate('If you set this option the results on the media list page will include subfolders.'); ?></p>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div id="panel2" class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a class="collapsed" href="#collapseTwo" data-target="#collapseTwo" data-toggle="collapse">
								<?php echo I18N::translate('Sort Topmenu items'); ?>
							</a>
						</h4>
					</div>
					<div class="panel-collapse collapse" id="collapseTwo">
						<div class="panel-heading">
							<?php echo I18N::translate('Click a row, then drag-and-drop to re-order the topmenu items. Then click the “save” button.'); ?>
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
			<button class="btn btn-primary" type="submit">
				<i class="fa fa-check"></i>
				<?php echo I18N::translate('Save'); ?>
			</button>
			<button class="btn btn-primary" type="reset" onclick="if (confirm('<?php echo I18N::translate('The settings will be reset to default. Are you sure you want to do this?'); ?>'))
						window.location.href = 'module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_reset';">
				<i class="fa fa-recycle"></i>
				<?php echo I18N::translate('Reset'); ?>
			</button>
		</form>
		<?php
	}

	// Implement ModuleConfigInterface
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	/**
	 * Make sure the database structure is up-to-date.
	 * Update database when updating from a version prior then version 1.5.2.1
	 * Version 1 update if the admin has logged in. A message will be shown to tell him all settings are reset to default.
	 * Old db-entries will be removed.
	 *
	 */
	protected static function updateSchema() {
		try {
			Database::updateSchema(WT_ROOT . WT_MODULES_DIR . 'justlight_theme_options/db_schema/', 'JL_SCHEMA_VERSION', 2);
		} catch (PDOException $ex) {
			// The schema update scripts should never fail.  If they do, there is no clean recovery.
			FlashMessages::addMessage($ex->getMessage(), 'danger');
			header('Location: ' . WT_BASE_URL . 'site-unavailable.php');
			throw $ex;
		}
	}

}

return new JustLightThemeOptionsModule;
