<?php
/*
 * webtrees: online genealogy
 * Copyright (C) 2016 webtrees development team
 * Copyright (C) 2016 JustCarmen
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace JustCarmen\WebtreesAddOns\JustLight;

use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Query\QueryMedia;
use Fisharebest\Webtrees\Tree;

/**
 * Class JustLight Theme Options
 */
class JustLightThemeOptionsClass extends JustLightThemeOptionsModule {

	// Get module options
	public function options($key) {
		if ($key === 'mediafolders') {
			return $this->listMediaFolders();
		} else {
			$JL_OPTIONS	 = unserialize($this->getSetting('JL_OPTIONS'));
			$key		 = strtoupper($key);
			if (empty($JL_OPTIONS) || (is_array($JL_OPTIONS) && !array_key_exists($key, $JL_OPTIONS))) {
				return $key === 'MENU' ? $this->getDefaultMenu() : $this->setDefault($key);
			} else {
				return $key === 'MENU' ? $this->menuJustLight($JL_OPTIONS['MENU']) : $JL_OPTIONS[$key];
			}
		}
	}

	protected function saveOptions() {
		$NEW_JL_OPTIONS			 = Filter::postArray('NEW_JL_OPTIONS');
		$NEW_JL_OPTIONS['MENU']	 = $this->sortArray(Filter::postArray('NEW_JL_MENU'), 'sort');
		$NEW_JL_OPTIONS['LOGO']  = Filter::post('JL_LOGO');
		
		$error			= false;		
		$image			= $_FILES['NEW_JL_LOGO'];
		$filename		= 'jl_' . $image['name'];
		$serverFileName = WT_DATA_DIR . $filename;
		
		if (!empty($image['name'])) {
			if ($this->upload($image, $serverFileName)) {
				$NEW_JL_OPTIONS['LOGO'] = $filename;
			} else {
				FlashMessages::addMessage(I18N::translate('Error: The logo you have uploaded is not a valid image! Your settings are not saved.'), 'warning');
				$error = true;
			}
		}
		
		if (!$error) {
			$this->setSetting('JL_OPTIONS', serialize($NEW_JL_OPTIONS));
			FlashMessages::addMessage(I18N::translate('Your settings are successfully saved.'), 'success');
			Log::addConfigurationLog($this->getTitle() . ' config updated');
		}
	}

	protected function menuJustLight($menulist) {
		$modules = array();
		foreach (Tree::getAll() as $tree) {
			$modules = array_merge(Module::getActiveMenus($tree), $modules);
		}

		// add newly activated modules to the menu
		$sort = count($menulist) + 1;
		foreach ($modules as $label => $module) {
			if (!array_key_exists($label, $menulist)) {
				$menulist[$label] = array(
					'title'		 => $module->getTitle(),
					'label'		 => $label,
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

	protected function listMenuJustLight($menulist) {
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

	/**
	 * Check if $module->getMenu does not return null
	 * Used on the configuration page
	 * 
	 * @global type $WT_TREE
	 * @param type $label
	 * @return boolean	 * 
	 */
	public function isMenu($label) {
		global $WT_TREE;
		$module = Module::getModuleByName($label);
		if (in_array($module, Module::getActiveMenus($WT_TREE))) {
			if ($module->getMenu()) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	// Set default module options
	private function setDefault($key) {
		$JL_DEFAULT = array(
			'LOGO'					 => '',
			'TITLESIZE'				 => '32',
			'COMPACT_MENU'			 => '0',
			'COMPACT_MENU_REPORTS'	 => '1',
			'MEDIA_MENU'			 => '0',
			'MEDIA_LINK'			 => '',
			'SHOW_SUBFOLDERS'		 => '1'
		);
		return $JL_DEFAULT[$key];
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

	private function listMediaFolders() {
		global $WT_TREE;

		$MEDIA_DIRECTORY				 = $WT_TREE->getPreference('MEDIA_DIRECTORY');
		$folders						 = QueryMedia::folderList();
		array_shift($folders);
		$folderlist[$MEDIA_DIRECTORY]	 = strtoupper(I18N::translate(substr($MEDIA_DIRECTORY, 0, -1)));

		foreach ($folders as $key => $value) {
			if (count(glob(WT_DATA_DIR . $MEDIA_DIRECTORY . $value . '*')) > 0) {
				$folder = array_filter(explode("/", $value));
				// only list first level folders
				if (count($folder) > 0 && !array_search($folder[0], $folderlist)) {
					$folderlist[$folder[0] . '/'] = I18N::translate($folder[0]);
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
			$return_array[$pos]['title']	 = $array[$pos]['title'];
			$return_array[$pos]['label']	 = $array[$pos]['label'];
			$return_array[$pos]['sort']		 = $array[$pos]['sort'];
			$return_array[$pos]['function']	 = $array[$pos]['function'];
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
	
	private function upload($image, $serverFileName) {
		// Check if we are dealing with a valid image
		if (!empty($image['name']) && preg_match('/^image\/(png|gif|jpeg)/', $image['type'])) {
			if ($this->options('logo')) {
				$this->deleteLogo(); // delete the old logo from the server.
			}
			
			move_uploaded_file($image['tmp_name'], $serverFileName);
			return true;
		} else {
			return false;
		}
	}
	
	protected function deleteLogo() {
		$filename = $this->options('logo');
		if (file_exists(WT_DATA_DIR . $filename)) {
			unlink(WT_DATA_DIR . $filename);
		}
	}

}
