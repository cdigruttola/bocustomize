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

class AdminLoginController extends AdminLoginControllerCore
{
    public function initContent()
    {
        parent::initContent();
        $this->context->smarty->assign([
            'shop_name' => Tools::safeOutput(Configuration::get(Bocustomize::BOCUSTOMIZE_TITLE_TEXT)),
            'copyright' => Configuration::get(Bocustomize::BOCUSTOMIZE_COPYRIGHT_TEXT),
            'custom_logo' => Configuration::get(Bocustomize::BOCUSTOMIZE_CUSTOM_LOGO),
            'ext' => Configuration::get(Bocustomize::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT),
            'module_dir' => __PS_BASE_URI__ . 'modules/bocustomize/',
            'social_icons' => Configuration::get(Bocustomize::BOCUSTOMIZE_SOCIAL_ICONS),
        ]);
    }

    public function createTemplate($tpl_name)
    {
        return $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . 'bocustomize/views/templates/admin/login/content.tpl', $this->context->smarty);
    }

}
