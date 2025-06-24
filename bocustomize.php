<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
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
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use cdigruttola\Bocustomize\Form\DataConfiguration\BoCustomizeConfigurationData;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Bocustomize extends Module
{
    private string $configurationSource;
    private array $fields;

    public function __construct()
    {
        $this->name = 'bocustomize';
        $this->tab = 'administration';
        $this->version = '3.0.0';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->configurationSource = _PS_MODULE_DIR_ . $this->name . '/config/configuration.json';

        parent::__construct();

        $this->displayName = $this->trans('BackOffice customize', [], 'Modules.Bocustomize.Main');
        $this->description = $this->trans('This module helps you to customize BackOffice', [], 'Modules.Bocustomize.Main');

        $this->ps_versions_compliancy = ['min' => '9.0.0', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install()
    {
        Configuration::updateValue(BoCustomizeConfigurationData::BOCUSTOMIZE_TITLE_TEXT, Configuration::get('PS_SHOP_NAME'));
        Configuration::updateValue(BoCustomizeConfigurationData::BOCUSTOMIZE_COPYRIGHT_TEXT, '© PrestaShop™ 2007-' . date('Y') . ' - All rights reserved');
        Configuration::updateValue(BoCustomizeConfigurationData::BOCUSTOMIZE_FILL_IMAGE_COLOR, '#FFF');

        return parent::install()
            && $this->registerHook('actionAdminLoginControllerSetMedia')
            && $this->importConfiguration();
    }

    protected function importConfiguration()
    {
        if (Tools::file_exists_no_cache($this->configurationSource)) {
            $configurationJson = Tools::file_get_contents($this->configurationSource);
            $this->fields = json_decode($configurationJson, true);

            return true;
        } else {
            return false;
        }
    }

    protected function exportConfiguration()
    {
        $configurationJson = json_encode($this->fields);
        if (@file_put_contents($this->configurationSource, $configurationJson)) {
            return true;
        }

        return false;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        Tools::redirectAdmin(SymfonyContainer::getInstance()->get('router')->generate('bocustomize_controller'));
    }

    public function hookActionAdminLoginControllerSetMedia($params)
    {
        if ($this->active && Configuration::get(BoCustomizeConfigurationData::BOCUSTOMIZE_CUSTOM_LOGO)) {
            $params['controller']->addCSS($this->_path . 'views/css/custom_logo.css');
        }
        $params['controller']->addJs($this->_path . 'views/js/admin/login.js');
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        if ($moduleManager->isEnabled('ps_accounts')) {
            $_GET['mode'] = 'local';
        }
    }
}
