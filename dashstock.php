<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Dashstock extends Module
{
	public function __construct()
	{
		$this->name = 'dashstock';
		$this->displayName = 'Dashboard Stock';
		$this->tab = 'dashboard';
		$this->version = '0.1';
		$this->author = 'Lyon Sun';
		$this->push_filename = _PS_CACHE_DIR_.'push/stock';
		$this->allow_push = true;
		$this->push_time_limit = 180;

		parent::__construct();
	}

	public function install()
	{
		return (parent::install()
			&& $this->registerHook('dashboardZoneOne')
			&& $this->registerHook('dashboardData')
			&& $this->registerHook('actionAdminControllerSetMedia')
		);
	}

	public function hookActionAdminControllerSetMedia()
	{
		if (get_class($this->context->controller) == 'AdminDashboardController')
		{
			if (method_exists($this->context->controller, 'addJquery'))
				$this->context->controller->addJquery();

			$this->context->controller->addJs($this->_path.'views/js/'.$this->name.'.js');
			$this->context->controller->addJs(
				array(
					_PS_JS_DIR_.'date.js',
					_PS_JS_DIR_.'tools.js'
				) // retro compat themes 1.5
			);
		}
	}

	public function hookDashboardZoneOne($params)
	{
		$gapi_mode = 'configure';
		if (!Module::isInstalled('gapi'))
			$gapi_mode = 'install';
		elseif (($gapi = Module::getInstanceByName('gapi')) && Validate::isLoadedObject($gapi) && $gapi->isConfigured())
			$gapi_mode = false;

		$this->context->smarty->assign(
			array(
				'gapi_mode' => $gapi_mode,
				'date_subtitle' => $this->l('(from %s to %s)'),
				'date_format' => $this->context->language->date_format_lite,
				'link' => $this->context->link,
			)
		);

		return $this->display(__FILE__, 'dashboard_zone_one.tpl');
	}

	public function hookDashboardData($params)
	{
		$total_stock_value = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			'
					SELECT SUM(sa.quantity)
					FROM `'._DB_PREFIX_.'stock_available` sa
		'
		);

		return array(
			'data_value' => array(
				'total_stock_value' => (int)$total_stock_value,
			),
		);
	}
}
