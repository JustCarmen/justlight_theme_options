<?php
/**
 * webtrees: online genealogy
* Copyright (C) 2018 JustCarmen (http://justcarmen.nl)
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
namespace JustCarmen\WebtreesAddOns\JustLight\Schema;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Schema\MigrationInterface;

/**
 * Upgrade the database schema from version 1 to version 2.
 */
class Migration1 implements MigrationInterface {

	/** {@inheritDoc} */
	public function upgrade() {
		// Reset configuration settings
		$rows = Database::prepare("SELECT * FROM `##module_setting` WHERE setting_name LIKE 'JL%'")->execute()->fetchAll();
		if (count($rows) > 0) {
			Database::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'JL%'")->execute();
			Log::addConfigurationLog('JustLight Theme Options reset to default values due to major changes in database scheme');
		}
	}

}
