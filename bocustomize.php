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
        $this->version = '2.0.0';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->configurationSource = _PS_MODULE_DIR_ . $this->name . '/config/configuration.json';

        parent::__construct();

        $this->displayName = $this->trans('BackOffice customize', [], 'Modules.Bocustomize.Main');
        $this->description = $this->trans('This module helps you to customize BackOffice', [], 'Modules.Bocustomize.Main');

        $this->ps_versions_compliancy = ['min' => '1.7.8', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install()
    {
        Configuration::updateValue(BoCustomizeConfigurationData::BOCUSTOMIZE_TITLE_TEXT, Configuration::get('PS_SHOP_NAME'));
        Configuration::updateValue(BoCustomizeConfigurationData::BOCUSTOMIZE_COPYRIGHT_TEXT, '&copy; PrestaShop&#8482; 2007-' . date('Y') . ' - All rights reserved');
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
        if (Module::isEnabled('ps_account')) {
            $_GET['mode'] = 'local';
        }
    }

    /**
     * Increases or decreases the brightness of a color by a percentage of the current brightness.
     *
     * @param string $hex_color Supported formats: `#FFF`, `#FFFFFF`, `FFF`, `FFFFFF`
     * @param float $percent A number between -1 and 1. E.g. 0.3 = 30% lighter; -0.4 = 40% darker.
     *
     * @return string
     */
    private function luminance($hex_color, $percent): string
    {
        if (strlen($hex_color) < 6) {
            $hex_color = $hex_color[0] . $hex_color[0] . $hex_color[1] . $hex_color[1] . $hex_color[2] . $hex_color[2];
        }
        $hex_color = array_map('hexdec', str_split(str_pad(str_replace('#', '', $hex_color), 6, '0'), 2));

        foreach ($hex_color as $i => $color) {
            $adjustableLimit = $percent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $percent);
            $hex_color[$i] = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }

        return '#' . implode($hex_color);
    }
}
