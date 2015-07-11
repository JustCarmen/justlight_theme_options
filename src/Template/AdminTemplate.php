<?php
/*
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * Copyright (C) 2015 JustCarmen
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
namespace JustCarmen\WebtreesAddOns\JustLight\Template;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use JustCarmen\WebtreesAddOns\JustLight\JustLightThemeOptionsClass;

class AdminTemplate extends JustLightThemeOptionsClass {

	protected function pageContent() {
		$controller = new PageController;
		return
			$this->pageHeader($controller) .
			$this->pageBody($controller);
	}

	private function pageHeader(PageController $controller) {
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
	}

	private function pageBody(PageController $controller) {
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
				<?php echo I18N::translate('save'); ?>
			</button>
			<button class="btn btn-primary" type="reset" onclick="if (confirm('<?php echo I18N::translate('The settings will be reset to default. Are you sure you want to do this?'); ?>'))
						window.location.href = 'module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_reset';">
				<i class="fa fa-recycle"></i>
				<?php echo I18N::translate('reset'); ?>
			</button>
		</form>
		<?php
	}

}
