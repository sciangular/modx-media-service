<?php

namespace Tsyfra\MediaService;

use Tsyfra\MediaService\ImageProcessorInterface;

final class ImagickImageProcessor implements ImageProcessorInterface
{
    public function generate(
        string $source,
        string $destination,
        array $options
    ): bool {
        if (!file_exists($source) || !is_readable($source) || !$destination) {
            return false;
        }

        $temporaryPath = $destination . '.tmp';

        $image = new \Imagick($source);

        $image->autoOrient();

        $image->cropThumbnailImage(
            $options['width'],
            $options['height']
        );

        $image->setImageFormat($options['format']);
        $image->setImageCompressionQuality($options['quality']);

        if ($options['strip'] ?? false) {
            $image->stripImage();
        }

        $image->writeImage($temporaryPath);
        rename($temporaryPath, $destination);
        $image->clear();
        return true;
    }
}
