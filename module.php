<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2016 webtrees development team
 * Copyright (C) 2016 JustCarmen
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
namespace JustCarmen\WebtreesAddOns\JustLight;

use Composer\Autoload\ClassLoader;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use JustCarmen\WebtreesAddOns\JustLight\Template\AdminTemplate;

define('JLO_VERSION', '1.7.4');

class JustLightThemeOptionsModule extends AbstractModule implements ModuleConfigInterface {
	
	// How to update the database schema for this module
	const SCHEMA_TARGET_VERSION = 2;
	const SCHEMA_SETTING_NAME = 'JL_SCHEMA_VERSION';
	const SCHEMA_MIGRATION_PREFIX = '\JustCarmen\WebtreesAddOns\JustLight\Schema';

	/** @var string location of the JustBlack Theme Options module files */
	var $directory;

	public function __construct() {
		parent::__construct('justlight_theme_options');

		$this->directory = WT_MODULES_DIR . $this->getName();

		// register the namespace
		$loader = new ClassLoader();
		$loader->addPsr4('JustCarmen\\WebtreesAddOns\\JustLight\\', $this->directory . '/src');
		$loader->register();
	}

	/**
	 * Get the module class.
	 * 
	 * Class functions are called with $this inside the source directory.
	 */
	private function module() {
		return new JustLightThemeOptionsClass;
	}

	// Extend Module
	public function getTitle() {
		return /* I18N: Name of a module  */ I18N::translate('JustLight Theme Options');
	}

	// Extend Module
	public function getDescription() {
		return /* I18N: Description of the module */ I18N::translate('Set options for the JustLight theme within the admin interface') . '<br><span class="small text-muted">' . I18N::translate('Version') . ' ' . JLO_VERSION . ' | by JustCarmen | <a href="http://www.justcarmen.nl/themes/justlight/">' . I18N::translate('Show details') . '</a></span>';
	}

	// Extend ModuleConfigInterface
	public function modAction($mod_action) {
		Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
		
		switch ($mod_action) {
			case 'admin_config':
				if (Filter::postBool('save') && Filter::checkCsrf()) {
					$this->module()->saveOptions();
				}
				$template = new AdminTemplate;
				return $template->pageContent();
			case 'admin_reset':
				Database::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JL%'")->execute();
				Log::addConfigurationLog($this->getTitle() . ' reset to default values');
				$template = new AdminTemplate;
				return $template->pageContent();
			default:
				http_response_code(404);
				break;
		}
	}

	// Implement ModuleConfigInterface
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

}

return new JustLightThemeOptionsModule;
