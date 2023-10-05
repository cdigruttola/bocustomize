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

use PrestaShop\PrestaShop\Core\Domain\Shop\DTO\ShopLogoSettings;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Bocustomize extends Module
{
    const BOCUSTOMIZE_CUSTOM_LOGO = 'BOCUSTOMIZE_CUSTOM_LOGO';
    const BOCUSTOMIZE_CUSTOM_LOGO_FILE = 'BOCUSTOMIZE_CUSTOM_LOGO_FILE';
    const BOCUSTOMIZE_CUSTOM_LOGO_CSS = 'BOCUSTOMIZE_CUSTOM_LOGO_CSS';
    const BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT = 'BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT';
    const BOCUSTOMIZE_SOCIAL_ICONS = 'BOCUSTOMIZE_SOCIAL_ICONS';
    const BOCUSTOMIZE_TITLE_TEXT = 'BOCUSTOMIZE_TITLE_TEXT';
    const BOCUSTOMIZE_COPYRIGHT_TEXT = 'BOCUSTOMIZE_COPYRIGHT_TEXT';
    const BOCUSTOMIZE_FILL_IMAGE_COLOR = 'BOCUSTOMIZE_FILL_IMAGE_COLOR';

    private string $configurationSource;
    private array $fields;

    public function __construct()
    {
        $this->name = 'bocustomize';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'cdigruttola';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->configurationSource = _PS_MODULE_DIR_ . $this->name . '/config/configuration.json';

        parent::__construct();

        $this->displayName = $this->trans('BackOffice customize', [], 'Modules.Bocustomize.Main');
        $this->description = $this->trans('This module helps you to customize BackOffice', [], 'Modules.Bocustomize.Main');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function install()
    {
        Configuration::updateValue(self::BOCUSTOMIZE_TITLE_TEXT, Configuration::get('PS_SHOP_NAME'));
        Configuration::updateValue(self::BOCUSTOMIZE_COPYRIGHT_TEXT, '&copy; PrestaShop&#8482; 2007-' . date('Y') . ' - All rights reserved');
        Configuration::updateValue(self::BOCUSTOMIZE_FILL_IMAGE_COLOR, '#FFF');

        return parent::install()
            && $this->registerHook('displayBackOfficeHeader')
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
        $output = '';
        if (Tools::isSubmit('submitBocustomizeLoginModule')) {
            if ($this->postProcessLogin()) {
                $output .= $this->displayConfirmation($this->trans('Settings updated succesfully', [], 'Modules.Bocustomize.Main'));
            } else {
                $output .= $this->displayError($this->trans('Error occurred during settings update', [], 'Modules.Bocustomize.Main'));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderLoginForm();
    }

    protected function renderLoginForm()
    {
        $helper = $this->getHelper();

        $helper->submit_action = 'submitBocustomizeLoginModule';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigLoginFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigLoginForm()]);
    }

    protected function getConfigLoginForm()
    {
        $ext = Configuration::get(self::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT);
        $image_url = _PS_MODULE_DIR_ . $this->name . '/views/img/admin_logo.' . $ext;

        return [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Login Settings', [], 'Modules.Bocustomize.Main'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Use custom logo', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_CUSTOM_LOGO,
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Enabled', [], 'Modules.Bocustomize.Main'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('Disabled', [], 'Modules.Bocustomize.Main'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->trans('Custom logo', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_CUSTOM_LOGO_FILE,
                        'accept' => 'image/png, image/jpg',
                        'display_image' => true,
                        'image' => file_exists($image_url) ? ImageManager::thumbnail($image_url, 'bocustomize_bologo.' . $ext, filesize($image_url) / 1000, $ext, true, true) : false,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Custom logo CSS', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_CUSTOM_LOGO_CSS,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Footer social icons', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_SOCIAL_ICONS,
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->trans('Enabled', [], 'Modules.Bocustomize.Main'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->trans('Disabled', [], 'Modules.Bocustomize.Main'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->trans('Title text', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_TITLE_TEXT,
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->trans('Copyright text', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_COPYRIGHT_TEXT,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->trans('Color used in ImageManager::resize to fill image', [], 'Modules.Bocustomize.Main'),
                        'name' => self::BOCUSTOMIZE_FILL_IMAGE_COLOR,
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.Bocustomize.Main'),
                ],
            ],
        ];
    }

    protected function getConfigLoginFormValues()
    {
        return [
            self::BOCUSTOMIZE_CUSTOM_LOGO => Configuration::get(self::BOCUSTOMIZE_CUSTOM_LOGO),
            self::BOCUSTOMIZE_CUSTOM_LOGO_CSS => Tools::file_get_contents(_PS_MODULE_DIR_ . $this->name . '/views/css/custom_logo.css'),
            self::BOCUSTOMIZE_SOCIAL_ICONS => Configuration::get(self::BOCUSTOMIZE_SOCIAL_ICONS),
            self::BOCUSTOMIZE_TITLE_TEXT => Configuration::get(self::BOCUSTOMIZE_TITLE_TEXT),
            self::BOCUSTOMIZE_COPYRIGHT_TEXT => Configuration::get(self::BOCUSTOMIZE_COPYRIGHT_TEXT),
            self::BOCUSTOMIZE_FILL_IMAGE_COLOR => Configuration::get(self::BOCUSTOMIZE_FILL_IMAGE_COLOR),
        ];
    }

    protected function postProcessLogin()
    {
        $form_values = $this->getConfigLoginFormValues();

        $res = true;
        foreach (array_keys($form_values) as $key) {
            if ($key == self::BOCUSTOMIZE_CUSTOM_LOGO_CSS) {
                $res &= @file_put_contents(_PS_MODULE_DIR_ . $this->name . '/views/css/custom_logo.css', Tools::getValue($key)) !== false;
            } else {
                $res &= Configuration::updateValue($key, Tools::getValue($key));
            }
        }
        $logo = $_FILES[self::BOCUSTOMIZE_CUSTOM_LOGO_FILE];
        if (!empty($logo['tmp_name']) && !ImageManager::validateUpload($logo, Tools::getMaxUploadSize(), ShopLogoSettings::AVAILABLE_LOGO_IMAGE_EXTENSIONS)) {
            $ext = substr($logo['name'], -3);
            if (move_uploaded_file($logo['tmp_name'], _PS_MODULE_DIR_ . $this->name . '/views/img/admin_logo.' . $ext)) {
                Configuration::updateValue(self::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT, $ext);
                $res &= true;
            }
        } elseif (!file_exists(_PS_MODULE_DIR_ . $this->name . '/views/img/admin_logo.' . Configuration::get(self::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT))) {
            $res &= false;
        }

        return $res;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if ($this->active) {
            // $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    public function hookActionAdminLoginControllerSetMedia($params)
    {
        if ($this->active && Configuration::get(self::BOCUSTOMIZE_CUSTOM_LOGO)) {
            $params['controller']->addCSS($this->_path . 'views/css/custom_logo.css');
        }
        $params['controller']->addJs($this->_path . 'views/js/admin/login.js');
    }

    /**
     * @return HelperForm
     */
    private function getHelper(): HelperForm
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper;
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
