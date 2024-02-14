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

namespace cdigruttola\Bocustomize\Form\DataConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BoCustomizeConfigurationData implements DataConfigurationInterface
{
    public const BOCUSTOMIZE_CUSTOM_LOGO = 'BOCUSTOMIZE_CUSTOM_LOGO';
    public const BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT = 'BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT';
    public const BOCUSTOMIZE_SOCIAL_ICONS = 'BOCUSTOMIZE_SOCIAL_ICONS';
    public const BOCUSTOMIZE_TITLE_TEXT = 'BOCUSTOMIZE_TITLE_TEXT';
    public const BOCUSTOMIZE_COPYRIGHT_TEXT = 'BOCUSTOMIZE_COPYRIGHT_TEXT';
    public const BOCUSTOMIZE_FILL_IMAGE_COLOR = 'BOCUSTOMIZE_FILL_IMAGE_COLOR';

    private const CONFIGURATION_FIELDS = [
        'custom_logo',
        'social_icons',
        'title_text',
        'copyright_text',
        'fill_image_color',
    ];

    /** @var ConfigurationInterface */
    private $configuration;
    /** @var \Bocustomize */
    private $module;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration, \Bocustomize $module)
    {
        $this->configuration = $configuration;
        $this->module = $module;
    }

    /**
     * @return OptionsResolver
     */
    protected function buildResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setDefined(self::CONFIGURATION_FIELDS)
            ->setAllowedTypes('custom_logo', 'bool')
            ->setAllowedTypes('social_icons', 'bool')
            ->setAllowedTypes('title_text', 'string')
            ->setAllowedTypes('copyright_text', 'string')
            ->setAllowedTypes('fill_image_color', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $return = [];

        $return['custom_logo'] = $this->configuration->get(self::BOCUSTOMIZE_CUSTOM_LOGO) ?? false;
        $return['social_icons'] = $this->configuration->get(self::BOCUSTOMIZE_SOCIAL_ICONS) ?? false;
        $return['title_text'] = $this->configuration->get(self::BOCUSTOMIZE_TITLE_TEXT) ?? '';
        $return['copyright_text'] = $this->configuration->get(self::BOCUSTOMIZE_COPYRIGHT_TEXT) ?? '';
        $return['fill_image_color'] = $this->configuration->get(self::BOCUSTOMIZE_FILL_IMAGE_COLOR) ?? '';
        $return['custom_logo_css'] = \Tools::file_get_contents(_PS_MODULE_DIR_ . $this->module->name . '/views/css/custom_logo.css');
        $ext = $this->configuration->get(self::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT);
        $image_url = _MODULE_DIR_ . $this->module->name . '/views/img/admin_logo.' . $ext;
        $return['custom_logo_file_preview'] = $image_url;

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        if ($this->validateConfiguration($configuration)) {
            $this->configuration->set(self::BOCUSTOMIZE_CUSTOM_LOGO, (bool) $configuration['custom_logo']);
            $this->configuration->set(self::BOCUSTOMIZE_SOCIAL_ICONS, (bool) $configuration['social_icons']);
            $this->configuration->set(self::BOCUSTOMIZE_TITLE_TEXT, $configuration['title_text']);
            $this->configuration->set(self::BOCUSTOMIZE_COPYRIGHT_TEXT, $configuration['copyright_text']);
            $this->configuration->set(self::BOCUSTOMIZE_FILL_IMAGE_COLOR, $configuration['fill_image_color']);

            @file_put_contents(_PS_MODULE_DIR_ . $this->module->name . '/views/css/custom_logo.css', $configuration['custom_logo_css']);
            if ($configuration['custom_logo_file']) {
                $this->upload($configuration['custom_logo_file']);
            }
        }

        return [];
    }

    public function upload(UploadedFile $file)
    {
        $this->configuration->set(self::BOCUSTOMIZE_CUSTOM_LOGO_FILE_EXT, $file->guessExtension());

        $fileName = 'admin_logo.' . $file->guessExtension();

        try {
            $file->move(_PS_MODULE_DIR_ . $this->module->name . '/views/img/', $fileName);
        } catch (FileException $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateConfiguration(array $configuration)
    {
        return isset($configuration['custom_logo']);
    }
}
