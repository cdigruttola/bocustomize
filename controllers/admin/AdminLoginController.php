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

    /**
     * @throws Exception
     * @throws SmartyException
     */
    public function display()
    {
        $this->context->smarty->assign([
            'display_header' => $this->display_header,
            'display_header_javascript' => $this->display_header_javascript,
            'display_footer' => $this->display_footer,
            'js_def' => Media::getJsDef(),
            'toggle_navigation_url' => $this->context->link->getAdminLink('AdminEmployees', true, [], [
                'action' => 'toggleMenu',
            ]),
        ]);

        // Use page title from meta_title if it has been set else from the breadcrumbs array
        if (!$this->meta_title) {
            $this->meta_title = $this->toolbar_title;
        }
        if (is_array($this->meta_title)) {
            $this->meta_title = strip_tags(implode(' ' . Configuration::get('PS_NAVIGATION_PIPE') . ' ', $this->meta_title));
        }
        $this->context->smarty->assign('meta_title', $this->meta_title);

        $template_dirs = $this->context->smarty->getTemplateDir();

        // Check if header/footer have been overridden
        $dir = $this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR . trim($this->override_folder, '\\/') . DIRECTORY_SEPARATOR;
        $module_list_dir = $this->context->smarty->getTemplateDir(0) . 'helpers' . DIRECTORY_SEPARATOR . 'modules_list' . DIRECTORY_SEPARATOR;

        $header_tpl = _PS_MODULE_DIR_ . 'bocustomize/views/templates/admin/login/header.tpl';
        $page_header_toolbar = file_exists($dir . 'page_header_toolbar.tpl') ? $dir . 'page_header_toolbar.tpl' : 'page_header_toolbar.tpl';
        $footer_tpl = file_exists($dir . 'footer.tpl') ? $dir . 'footer.tpl' : 'footer.tpl';
        $modal_module_list = file_exists($module_list_dir . 'modal.tpl') ? $module_list_dir . 'modal.tpl' : '';
        $tpl_action = $this->tpl_folder . $this->display . '.tpl';

        // Check if action template has been overridden
        foreach ($template_dirs as $template_dir) {
            if (file_exists($template_dir . DIRECTORY_SEPARATOR . $tpl_action) && $this->display != 'view' && $this->display != 'options') {
                if (method_exists($this, $this->display . Tools::toCamelCase($this->className))) {
                    $this->{$this->display . Tools::toCamelCase($this->className)}();
                }
                $this->context->smarty->assign('content', $this->context->smarty->fetch($tpl_action));

                break;
            }
        }

        if (!$this->ajax) {
            $template = $this->createTemplate($this->template);
            $page = $template->fetch();
        } else {
            $page = $this->content;
        }

        if ($conf = Tools::getValue('conf')) {
            $this->context->smarty->assign('conf', $this->json ? json_encode($this->_conf[(int) $conf]) : $this->_conf[(int) $conf]);
        }

        if ($error = Tools::getValue('error')) {
            $this->context->smarty->assign('error', $this->json ? json_encode($this->_error[(int) $error]) : $this->_error[(int) $error]);
        }

        foreach (['errors', 'warnings', 'informations', 'confirmations'] as $type) {
            if (!is_array($this->$type)) {
                $this->$type = (array) $this->$type;
            }
            $this->context->smarty->assign($type, $this->json ? json_encode(array_unique($this->$type)) : array_unique($this->$type));
        }

        if ($this->show_page_header_toolbar && !$this->lite_display) {
            $this->context->smarty->assign(
                [
                    'page_header_toolbar' => $this->context->smarty->fetch($page_header_toolbar),
                ]
            );
            if (!empty($modal_module_list)) {
                $this->context->smarty->assign(
                    [
                        'modal_module_list' => $this->context->smarty->fetch($modal_module_list),
                    ]
                );
            }
        }

        $this->context->smarty->assign('baseAdminUrl', __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) . '/');

        $this->context->smarty->assign(
            [
                'page' => $this->json ? json_encode($page) : $page,
                'header' => $this->context->smarty->fetch($header_tpl),
                'footer' => $this->context->smarty->fetch($footer_tpl),
            ]
        );

        $this->smartyOutputContent($this->layout);
    }
}
