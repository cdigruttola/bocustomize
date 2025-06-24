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

declare(strict_types=1);

namespace cdigruttola\Bocustomize\Controller;

use cdigruttola\Bocustomize\Form\DataConfiguration\BoCustomizeConfigurationData;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShopBundle\Controller\Admin\LoginController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Tools;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BoCustomizeLoginController extends LoginController
{

    public function __construct(
        private readonly ShopContext $shopContext,
        private readonly string $projectDir,
        private readonly string $adminFolderName,
        private readonly Configuration $configuration,
    ) {
        parent::__construct($this->shopContext, $this->projectDir, $this->adminFolderName);
    }

    protected function renderLoginPage(FormInterface $loginForm, FormInterface $requestPasswordResetForm, bool $showRequestPasswordResetForm): Response
    {
        return $this->render('@PrestaShop/Admin/Login/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'requestPasswordResetForm' => $requestPasswordResetForm->createView(),
            'showRequestPasswordResetForm' => $showRequestPasswordResetForm,
            'imgDir' => $this->shopContext->getBaseURI() . 'img/',
            'shopName' => Tools::safeOutput($this->configuration->get(BoCustomizeConfigurationData::BOCUSTOMIZE_TITLE_TEXT, $this->getConfiguration()->get('PS_SHOP_NAME'))),
            'copyright' => $this->configuration->get(BoCustomizeConfigurationData::BOCUSTOMIZE_COPYRIGHT_TEXT),
            'custom_logo' => $this->configuration->get(BoCustomizeConfigurationData::BOCUSTOMIZE_CUSTOM_LOGO),
            'ext' => $this->configuration->get(BoCustomizeConfigurationData::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT),
            'module_dir' => __PS_BASE_URI__ . 'modules/bocustomize/',
            'social_icons' => $this->configuration->get(BoCustomizeConfigurationData::BOCUSTOMIZE_SOCIAL_ICONS),

        ]);
    }
}
