<?php

declare(strict_types=1);

namespace Tsyfra\MediaService;

class ImageProcessor
{
    public static array $MIME_TYPE_EXT = [
        'image/avif'  => 'avif',
        'image/webp'  => 'webp',
        'image/png'   => 'png',
        'image/jpeg'   => 'jpg',
        'image/gif'   => 'gif',
    ];

    public function __construct(private readonly array $config) {}

    public function getResponsiveImage(string $src, string $mediaVariant, array $attributes = []): string
    {
        $mediaConfig = $this->config['mediaRegistry'][$mediaVariant] ?? [];
        if (empty($mediaConfig)) {
            return '';
        }

        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= sprintf(' %s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES));
        }

        $sourceString = '';
        foreach ($mediaConfig['source'] ?? [] as $sourceConfig) {
            $sourceString .= $this->makeSource($src, $sourceConfig);
        }

        $imageString = $this->makeImg(
            $src,
            [
                ...$mediaConfig['img'] ?? [],
                'attributes' => $attributes,
            ]
        );

        return $sourceString
            ? sprintf('<picture>%s%s</picture>', $sourceString, $imageString)
            : $imageString;
    }

    private function makeMediaCacheFilepath(string $path, string $mediaSizeKey, string $mimeType): string
    {
        $mediaExtension = self::$MIME_TYPE_EXT[$mimeType] ?? '';

        $pathInfo = pathinfo($path);
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';

        $mediaBasePath = $this->config['mediaBasePath'] ?? '';
        $mediaRelativeDirname = str_starts_with($dirname, $mediaBasePath)
            ? substr($dirname, strlen($mediaBasePath))
            : $dirname;

        return $this->config['mediaCachePath']
            . $mediaRelativeDirname . DIRECTORY_SEPARATOR
            . $filename . '.' . $mediaSizeKey
            . ($mediaExtension ? '.' . $mediaExtension : '');
    }

    private function makeAttrString(array $attributes): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= sprintf(' %s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES));
        }
        return $attrString;
    }

    private function makeSrcSet(string $filepath, string $mimeType, array $mediaSizes): string
    {
        $srcSet = [];
        foreach ($mediaSizes as $mediaSizeKey) {
            $src = $this->makeMediaCacheFilepath(
                $filepath,
                (string)$mediaSizeKey,
                $mimeType
            );
            $srcSet[] = sprintf(
                '%s %sw',
                htmlspecialchars($src, ENT_QUOTES),
                htmlspecialchars((string)$this->config['mediaSizeMap'][$mediaSizeKey]['width'] ?? '', ENT_QUOTES)
            );
        }
        return implode(', ', $srcSet);
    }

    private function makeSource(string $filepath, array $sourceConfig): string
    {
        $mimeType = $sourceConfig['type'] ?? '';
        $srcset = $this->makeSrcSet($filepath, $mimeType, $sourceConfig['srcset']);
        $media = $sourceConfig['media'] ?? '';
        $sizes = $sourceConfig['sizes'] ?? '';
        $dimensions = $this->config['mediaSizeMap'][$sourceConfig['mediaSizeKey'] ?? ''] ?? null;

        $attributes = [];
        if ($media) {
            $attributes['media'] = $media;
        }
        if ($mimeType) {
            $attributes['type'] = $mimeType;
        }
        if ($srcset) {
            $attributes['srcset'] = $srcset;
        }
        if ($sizes) {
            $attributes['sizes'] = $sizes;
        }
        if ($dimensions) {
            $attributes['width'] = (string)$dimensions['width'];
            $attributes['height'] = (string)$dimensions['height'];
        }

        return sprintf('<source%s>', $this->makeAttrString($attributes));
    }

    private function makeImg(string $filepath, array $imgConfig): string
    {
        $mimeType = $imgConfig['type'] ?? '';
        $src = $this->makeMediaCacheFilepath(
            $filepath,
            (string)$imgConfig['src'] ?? '',
            $mimeType
        );
        $srcset = $this->makeSrcSet($filepath, $mimeType, $imgConfig['srcset']);
        $sizes = $imgConfig['sizes'] ?? '';
        $dimensions = $this->config['mediaSizeMap'][$imgConfig['src'] ?? ''] ?? null;

        $attributes = [];
        if ($src) {
            $attributes['src'] = $src;
        }
        if ($srcset) {
            $attributes['srcset'] = $srcset;
        }
        if ($sizes) {
            $attributes['sizes'] = $sizes;
        }
        if ($dimensions) {
            $attributes['width'] = (string)$dimensions['width'];
            $attributes['height'] = (string)$dimensions['height'];
        }

        foreach ($imgConfig['attributes'] as $key => $value) {
            $attributes[$key] = (string)$value;
        }

        return sprintf('<img%s>', $this->makeAttrString($attributes));
    }
}
