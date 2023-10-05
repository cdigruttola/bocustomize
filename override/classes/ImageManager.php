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
class ImageManager extends ImageManagerCore
{
    /**
     * {@inheritdoc}
     */
    public static function resize(
        $sourceFile,
        $destinationFile,
        $destinationWidth = null,
        $destinationHeight = null,
        $destinationFileType = 'jpg',
        $forceType = false,
        &$error = 0,
        &$targetWidth = null,
        &$targetHeight = null,
        $quality = 5,
        &$sourceWidth = null,
        &$sourceHeight = null
    ) {
        clearstatcache(true, $sourceFile);

        // Check if original file exists
        if (!file_exists($sourceFile) || !filesize($sourceFile)) {
            $error = self::ERROR_FILE_NOT_EXIST;

            return false;
        }

        list($tmpWidth, $tmpHeight, $sourceFileType) = getimagesize($sourceFile);
        $rotate = 0;
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourceFile);

            if ($exif && isset($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $sourceWidth = $tmpWidth;
                        $sourceHeight = $tmpHeight;
                        $rotate = 180;

                        break;

                    case 6:
                        $sourceWidth = $tmpHeight;
                        $sourceHeight = $tmpWidth;
                        $rotate = -90;

                        break;

                    case 8:
                        $sourceWidth = $tmpHeight;
                        $sourceHeight = $tmpWidth;
                        $rotate = 90;

                        break;

                    default:
                        $sourceWidth = $tmpWidth;
                        $sourceHeight = $tmpHeight;
                }
            } else {
                $sourceWidth = $tmpWidth;
                $sourceHeight = $tmpHeight;
            }
        } else {
            $sourceWidth = $tmpWidth;
            $sourceHeight = $tmpHeight;
        }

        // If the filetype is not forced and we are requesting a JPG file, we must
        // adjust the format inside according to PS_IMAGE_QUALITY in some cases.
        if (!$forceType && $destinationFileType === 'jpg') {
            if (Configuration::get('PS_IMAGE_QUALITY') == 'png_all'
                || (Configuration::get('PS_IMAGE_QUALITY') == 'png' && $sourceFileType == IMAGETYPE_PNG)) {
                $destinationFileType = 'png';
            }

            if (Configuration::get('PS_IMAGE_QUALITY') == 'webp_all'
                || (Configuration::get('PS_IMAGE_QUALITY') == 'webp' && $sourceFileType == IMAGETYPE_WEBP)) {
                $destinationFileType = 'webp';
            }
        }

        if (!$sourceWidth) {
            $error = self::ERROR_FILE_WIDTH;

            return false;
        }
        if (!$destinationWidth) {
            $destinationWidth = $sourceWidth;
        }
        if (!$destinationHeight) {
            $destinationHeight = $sourceHeight;
        }

        $widthDiff = $destinationWidth / $sourceWidth;
        $heightDiff = $destinationHeight / $sourceHeight;

        $psImageGenerationMethod = Configuration::get('PS_IMAGE_GENERATION_METHOD');
        if ($widthDiff > 1 && $heightDiff > 1) {
            $nextWidth = $sourceWidth;
            $nextHeight = $sourceHeight;
        } else {
            if ($psImageGenerationMethod == 2 || (!$psImageGenerationMethod && $widthDiff > $heightDiff)) {
                $nextHeight = $destinationHeight;
                $nextWidth = round(($sourceWidth * $nextHeight) / $sourceHeight);
                $destinationWidth = (int) (!$psImageGenerationMethod ? $destinationWidth : $nextWidth);
            } else {
                $nextWidth = $destinationWidth;
                $nextHeight = round($sourceHeight * $destinationWidth / $sourceWidth);
                $destinationHeight = (int) (!$psImageGenerationMethod ? $destinationHeight : $nextHeight);
            }
        }

        if (!ImageManager::checkImageMemoryLimit($sourceFile)) {
            $error = self::ERROR_MEMORY_LIMIT;

            return false;
        }

        $targetWidth = $destinationWidth;
        $targetHeight = $destinationHeight;

        $destImage = imagecreatetruecolor($destinationWidth, $destinationHeight);
        $color = self::hex2rgb(Configuration::get(Bocustomize::BOCUSTOMIZE_FILL_IMAGE_COLOR));
        // If the output is PNG, fill with transparency. Else fill with white background.
        if ($destinationFileType == 'png' || $destinationFileType == 'webp' || $destinationFileType == 'avif') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, $color['red'], $color['green'], $color['blue'], 127);
            imagefilledrectangle($destImage, 0, 0, $destinationWidth, $destinationHeight, $transparent);
        } else {
            $white = imagecolorallocate($destImage, $color['red'], $color['green'], $color['blue']);
            imagefilledrectangle($destImage, 0, 0, $destinationWidth, $destinationHeight, $white);
        }

        $srcImage = ImageManager::create($sourceFileType, $sourceFile);
        if ($rotate) {
            /** @phpstan-ignore-next-line */
            $srcImage = imagerotate($srcImage, $rotate, 0);
        }

        if ($destinationWidth >= $sourceWidth && $destinationHeight >= $sourceHeight) {
            imagecopyresized($destImage, $srcImage, (int) (($destinationWidth - $nextWidth) / 2), (int) (($destinationHeight - $nextHeight) / 2), 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight);
        } else {
            ImageManager::imagecopyresampled($destImage, $srcImage, (int) (($destinationWidth - $nextWidth) / 2), (int) (($destinationHeight - $nextHeight) / 2), 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight, $quality);
        }
        $writeFile = ImageManager::write($destinationFileType, $destImage, $destinationFile);
        Hook::exec('actionOnImageResizeAfter', ['dst_file' => $destinationFile, 'file_type' => $destinationFileType]);
        @imagedestroy($srcImage);

        file_put_contents(
            dirname($destinationFile) . DIRECTORY_SEPARATOR . 'fileType',
            $destinationFileType
        );

        return $writeFile;
    }

    private static function hex2rgb($hex): array
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return ['red' => $r, 'green' => $g, 'blue' => $b];
    }
}
