<?php

namespace Tsyfra\MediaService;

use Tsyfra\MediaService\ImageProcessorInterface;

final class ImagickImageProcessor implements ImageProcessorInterface
{
    public function generate(
        string $source,
        string $destination,
        array $options
    ): array {
        if (!file_exists($source) || !is_readable($source) || !$destination) {
            return [];
        }

        $temporaryPath = $destination . '.tmp';
        $dirname = dirname($temporaryPath);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0775, true);
        }

        $image = new \Imagick($source);

        $image->autoOrient();

        if ($options['width'] && $options['height']) {
            $image->cropThumbnailImage(
                $options['width'],
                $options['height']
            );
        } else {
            $image->thumbnailImage($options['width'] ?? 0, $options['height'] ?? 0);
        }


        $image->setImageFormat($options['format']);
        $image->setImageCompressionQuality($options['quality']);

        if ($options['strip'] ?? false) {
            $image->stripImage();
        }

        if (isset($options['watermark'])) {
            $image = $this->applyWatermark($image, $options['watermark']);
        }

        $image->writeImage($temporaryPath);
        rename($temporaryPath, $destination);
        $result = [
            'intrinsicWidth' => $image->getImageWidth(),
            'intrinsicHeight' => $image->getImageHeight(),
        ];
        $image->clear();

        return $result;
    }

    private function applyWatermark(\Imagick $image, array $watermarkConfig): \Imagick
    {
        $watermarkPath = $watermarkConfig['path'];
        if (!file_exists($watermarkPath) || !is_readable($watermarkPath)) {
            return $image;
        }

        [$vPlacement, $hPlacement] = explode('-', $watermarkConfig['placement']) ?? ['top', 'left'];
        $margin = $watermarkConfig['margin'] ?? 0;
        $opacity = $watermarkConfig['opacity'] ?? 1.0;
        $width = $watermarkConfig['width'] ?? 48;

        $watermark = new \Imagick($watermarkPath);
        $watermark->thumbnailImage($width, 0);
        if ($opacity < 1.0) {
            $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);
        }
        $watermark->setImageFormat('png');

        switch ($vPlacement) {
            case 'top':
                $yPosition = $margin;
                break;
            case 'bottom':
                $yPosition = $image->getImageHeight() - $watermark->getImageHeight() - $margin;
                break;
            default:
                $yPosition = 0;
        }

        switch ($hPlacement) {
            case 'left':
                $xPosition = $margin;
                break;
            case 'right':
                $xPosition = $image->getImageWidth() - $watermark->getImageWidth() - $margin;
                break;
            default:
                $xPosition = 0;
        }

        $image->compositeImage(
            $watermark,
            \Imagick::COMPOSITE_OVER,
            $xPosition,
            $yPosition
        );
        $watermark->clear();

        return $image;
    }
}
