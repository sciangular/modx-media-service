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
        $result = [
            'intrinsicWidth' => $image->getImageWidth(),
            'intrinsicHeight' => $image->getImageHeight(),
        ];
        $image->clear();

        return $result;
    }
}
