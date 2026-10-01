<?php

declare(strict_types=1);

namespace Tsyfra\MediaService;

class ImageProcessor
{
    public static array $MIME_TYPES = [
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

    private function makeMediaCacheFilepath(string $path, array $config): string
    {
        $mediaSizeKey = $config['mediaSizeKey'] ?? '';
        $mediaExtension = $config['mediaExtension'] ?? '';

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

    private function makeSrcSet(string $filepath, string $mimeType, array $mediaSizes): string
    {
        $srcSet = [];
        foreach ($mediaSizes as $mediaSizeKey) {
            $src = $this->makeMediaCacheFilepath(
                $filepath,
                [
                    'mediaExtension' => self::$MIME_TYPES[$mimeType] ?? '',
                    'mediaSizeKey' => (string)$mediaSizeKey
                ]
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

        $attrString = '';
        $attrString .= $media ? sprintf(' media="%s"', htmlspecialchars($media, ENT_QUOTES)) : '';
        $attrString .= $mimeType ? sprintf(' type="%s"', htmlspecialchars($mimeType, ENT_QUOTES)) : '';
        $attrString .= $srcset ? sprintf(' srcset="%s"', htmlspecialchars($srcset, ENT_QUOTES)) : '';
        $attrString .= $sizes ? sprintf(' sizes="%s"', htmlspecialchars($sizes, ENT_QUOTES)) : '';
        $attrString .= $dimensions ? sprintf(
            ' width="%s" height="%s"',
            htmlspecialchars((string)$dimensions['width'], ENT_QUOTES),
            htmlspecialchars((string)$dimensions['height'], ENT_QUOTES)
        ) : '';

        return sprintf('<source%s>', $attrString);
    }

    private function makeImg(string $filepath, array $imgConfig): string
    {
        $mimeType = $imgConfig['type'] ?? '';
        $src = $this->makeMediaCacheFilepath(
            $filepath,
            [
                'mediaExtension' => self::$MIME_TYPES[$mimeType] ?? '',
                'mediaSizeKey' => (string)$imgConfig['src'] ?? ''
            ]
        );
        $srcset = $this->makeSrcSet($filepath, $mimeType, $imgConfig['srcset']);
        $sizes = $imgConfig['sizes'] ?? '';
        $dimensions = $this->config['mediaSizeMap'][$imgConfig['src'] ?? ''] ?? null;

        $attrString = '';
        $attrString .= $src ? sprintf(' src="%s"', htmlspecialchars($src, ENT_QUOTES)) : '';
        $attrString .= $srcset ? sprintf(' srcset="%s"', htmlspecialchars($srcset, ENT_QUOTES)) : '';
        $attrString .= $sizes ? sprintf(' sizes="%s"', htmlspecialchars($sizes, ENT_QUOTES)) : '';
        $attrString .= $dimensions ? sprintf(
            ' width="%s" height="%s"',
            htmlspecialchars((string)$dimensions['width'], ENT_QUOTES),
            htmlspecialchars((string)$dimensions['height'], ENT_QUOTES)
        ) : '';

        foreach ($imgConfig['attributes'] as $key => $value) {
            $attrString .= sprintf(' %s="%s"', htmlspecialchars($key, ENT_QUOTES), htmlspecialchars((string)$value, ENT_QUOTES));
        }

        return sprintf('<img%s>', $attrString);
    }
}
