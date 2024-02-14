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

if (!defined('_PS_VERSION_')) {
    exit;
}

use cdigruttola\MultiSellerMarketplace\Entity\MultiSellerMarketplaceCommission;
use cdigruttola\MultiSellerMarketplace\Entity\MultiSellerMarketplaceSeller;
use cdigruttola\MultiSellerMarketplace\Entity\MultiSellerMarketplaceSellerEmployee;
use cdigruttola\MultiSellerMarketplace\Entity\MultiSellerMarketplaceSellerOrderReturnState;
use cdigruttola\MultiSellerMarketplace\Entity\MultiSellerMarketplaceSellerOrderState;
use cdigruttola\MultiSellerMarketplace\Exceptions\MissingSellerBankDetailsException;
use cdigruttola\MultiSellerMarketplace\Exceptions\SellerNotFoundException;
use cdigruttola\MultiSellerMarketplace\Filter\CommissionFilters;
use cdigruttola\MultiSellerMarketplace\Filter\SellerRequestPayoutFilters;
use cdigruttola\MultiSellerMarketplace\Repository\MultiSellerMarketplaceSellerEmployeeRepository;
use cdigruttola\MultiSellerMarketplace\Repository\MultiSellerMarketplaceSellerRepository;
use cdigruttola\MultiSellerMarketplace\Translations\TranslationDomains;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BoCustomizeController extends FrameworkBundleAdminController
{
    /**
     * @var array
     */
    private $languages;

    public function __construct($languages)
    {
        parent::__construct();
        $this->languages = $languages;
    }

    public function index(): Response
    {

        $configurationForm = $this->get('cdigruttola.bocustomize.form.configuration_type.form_handler')->getForm();

        return $this->render('@Modules/bocustomize/views/templates/admin/index.html.twig', [
            'form' => $configurationForm->createView(),
            'help_link' => false,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveConfiguration(Request $request): Response
    {
        $redirectResponse = $this->redirectToRoute('bocustomize_controller');

        $form = $this->get('cdigruttola.bocustomize.form.configuration_type.form_handler')->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $this->get('cdigruttola.bocustomize.form.configuration_type.form_handler')->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->flashErrors($formErrors);

        return $redirectResponse;
    }

}
