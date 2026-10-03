<?php

declare(strict_types=1);

namespace Tsyfra\MediaService;

class ImageProcessor
{
    private string $mediaBasePath;
    private string $mediaCachePath;
    private string $fingerprintCachePath;
    private array $processingQueue = [];

    private static array $MIME_TYPE_EXT = [
        'image/avif' => 'avif',
        'image/webp' => 'webp',
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/gif' => 'gif',
    ];

    public function __construct(
        private readonly string $ROOT_PATH,
        private ImageProcessorInterface $processor,
        private readonly array $config
    ) {
        $this->mediaBasePath = $config['mediaBasePath'];
        $this->mediaCachePath = $config['mediaCachePath'];
        $this->fingerprintCachePath = $config['fingerprintCachePath'];
    }

    public function getResponsiveImage(array $artVariantSrcs, string $mediaVariant, array $attributes = []): string
    {
        $mediaConfig = $this->config['mediaRegistry'][$mediaVariant] ?? [];
        if (!$mediaConfig || !$artVariantSrcs) {
            return '';
        }

        $sourceOutput = '';
        foreach ($mediaConfig['source'] ?? [] as $sourceConfig) {
            $sourceOutput .= $this->makeSourceMarkup($artVariantSrcs, $sourceConfig);
        }

        $imageString = $this->makeImgMarkup(
            $artVariantSrcs,
            [
                ...$mediaConfig['img'] ?? [],
                'attributes' => $attributes,
            ]
        );

        $this->processQueue();
        // print_r($this->processingQueue);

        return $sourceOutput
            ? sprintf('<picture>%s%s</picture>', $sourceOutput, $imageString)
            : $imageString;
    }

    private function makeSourceMarkup(array $artVariantSrcs, array $sourceConfig): string
    {
        $artVariantKey = $sourceConfig['artVariant'] ?? null;
        $mimeType = $sourceConfig['mimeType'] ?? null;
        if (!$artVariantKey || !$mimeType) {
            return '';
        }

        $srcset = $this->makeSrcsetString(
            $artVariantSrcs,
            $artVariantKey,
            $mimeType,
            $sourceConfig['srcsetSizes']
        );
        $media = $sourceConfig['mediaAttr'] ?? '';
        $sizes = $sourceConfig['sizesAttr'] ?? '';
        $defaultSize = $this->config['artVariantRegistry'][$artVariantKey]['sizes'][$sourceConfig['defaultSize']] ?? null;

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
        if ($defaultSize) {
            $attributes['width'] = (string)$defaultSize['width'];
            $attributes['height'] = (string)$defaultSize['height'];
        }

        return sprintf('<source%s>', $this->makeAttrString($attributes));
    }

    private function makeImgMarkup(array $artVariantSrcs, array $imgConfig): string
    {
        $artVariantKey = $imgConfig['artVariant'] ?? null;
        $sizeKey = $imgConfig['srcSize'] ?? null;
        $mimeType = $imgConfig['mimeType'] ?? '';
        if (!$artVariantKey || !$sizeKey || !$mimeType) {
            return '';
        }

        $originPath = $this->resolveOriginPath($artVariantSrcs, $artVariantKey);
        /** 
         * $canonnicalPath - by convention, the first source path from the array of art variant sources, used as the base for generating the srcset.
         */
        $canonnicalPath = $artVariantSrcs[0];
        $destinationPath = $this->generateMediaCacheFilepath(
            $canonnicalPath,
            "{$artVariantKey}-{$sizeKey}",
            $mimeType
        );
        $srcset = $this->makeSrcsetString(
            $artVariantSrcs,
            $artVariantKey,
            $mimeType,
            $imgConfig['srcsetSizes']
        );
        $sizes = $imgConfig['sizesAttr'] ?? '';
        $defaultSize = $this->config['artVariantRegistry'][$artVariantKey]['sizes'][$imgConfig['srcSize']] ?? null;

        $attributes = [];
        if ($srcset) {
            $attributes['srcset'] = $srcset;
        }
        if ($destinationPath) {
            $attributes['src'] = $destinationPath;
            $this->enqueueArtVariant(
                $originPath,
                $destinationPath,
                $artVariantKey,
                $sizeKey,
                $mimeType
            );
        }
        if ($sizes) {
            $attributes['sizes'] = $sizes;
        }
        if ($defaultSize) {
            $attributes['width'] = (string)$defaultSize['width'];
            $attributes['height'] = (string)$defaultSize['height'];
        }

        foreach ($imgConfig['attributes'] as $key => $value) {
            $attributes[$key] = (string)$value;
        }

        return sprintf('<img%s>', $this->makeAttrString($attributes));
    }

    private function makeSrcsetString(
        array $artVariantSrcs,
        string $artVariantKey,
        string $mimeType,
        array $sizes
    ): string {
        $originPath = $this->resolveOriginPath($artVariantSrcs, $artVariantKey);
        $canonnicalPath = $artVariantSrcs[0];
        $srcSet = [];

        foreach ($sizes as $sizeKey) {
            $width = (string) $this->config['artVariantRegistry'][$artVariantKey]['sizes'][$sizeKey]['width'] ?? '';
            if (!$width) continue;

            $destinationPath = $this->generateMediaCacheFilepath(
                $canonnicalPath,
                "{$artVariantKey}-{$sizeKey}",
                $mimeType
            );
            $srcSet[] = sprintf(
                '%s %sw',
                htmlspecialchars($destinationPath, ENT_QUOTES),
                htmlspecialchars($width, ENT_QUOTES)
            );
            $this->enqueueArtVariant(
                $originPath,
                $destinationPath,
                $artVariantKey,
                $sizeKey,
                $mimeType
            );
        }
        return implode(', ', $srcSet);
    }

    private function makeAttrString(array $attributes): string
    {
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= sprintf(' %s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES));
        }
        return $attrString;
    }

    private function generateMediaCacheFilepath(
        string $filepath,
        string $variantSuffix,
        string $mimeType
    ): string {
        $mediaExtension = self::$MIME_TYPE_EXT[$mimeType] ?? '';

        $pathInfo = pathinfo($filepath);
        $dirname = $pathInfo['dirname'] ?? '';
        $filename = $pathInfo['filename'] ?? '';

        $relativeDirname = str_starts_with($dirname, $this->mediaBasePath)
            ? substr($dirname, strlen($this->mediaBasePath))
            : $dirname;

        return ($this->mediaCachePath ?? '')
            . $relativeDirname . DIRECTORY_SEPARATOR
            . $filename . '.' . $variantSuffix
            . ($mediaExtension ? '.' . $mediaExtension : '');
    }

    private function fsPath(string $filepath): string
    {
        return $this->ROOT_PATH . trim($filepath, '/');
    }

    private function resolveOriginPath(array $artVariantSrcs, string $variantKey): string
    {
        return $artVariantSrcs[$this->config['artVariantRegistry'][$variantKey]['srcIndex']] ?? $artVariantSrcs[0];
    }

    private function enqueueArtVariant(
        string $origin,
        string $destination,
        string $variantKey,
        string $sizeKey,
        string $mimeType
    ): void {
        $variantOptions = $this->config['artVariantRegistry'][$variantKey]['sizes'][$sizeKey] ?? [];

        $width = $variantOptions['width'] ?? '';
        $height = $variantOptions['height'] ?? '';
        $quality = $variantOptions['quality'] ?? $this->config['artVariantRegistry'][$variantKey]['quality'] ?? 75;
        $strip = $variantOptions['strip'] ?? $this->config['artVariantRegistry'][$variantKey]['strip'] ?? true;

        $fingerprint = hash('sha256', $origin . $destination . $variantKey . $sizeKey . $mimeType);
        $this->processingQueue[$fingerprint] = [
            'origin' => $origin,
            'destination' => $destination,
            'originPath' => $this->fsPath($origin),
            'destinationPath' => $this->fsPath($destination),
            'options' => [
                'strip' => $strip,
                'width' => $width,
                'height' => $height,
                'quality' => $quality,
                'format' => self::$MIME_TYPE_EXT[$mimeType] ?? 'webp',
            ]
        ];
    }

    private function processQueue(): void
    {
        $metadata = [];

        foreach ($this->processingQueue as $fingerprint => $task) {
            $generated = false;
            if (!$this->isVariantUpToDate($task['originPath'], $task['destinationPath'], $task['options'])) {
                $generated = $this->processor->generate(
                    $task['originPath'],
                    $task['destinationPath'],
                    $task['options']
                );
            }
            unset($this->processingQueue[$fingerprint]);
            if ($generated) {
                $metadata[$task['origin']][$task['destination']] = $this->generateFingerprint($task['originPath'], $task['options']);
            }
        }

        $this->updateMetadata($metadata);
    }

    private function generateFingerprint(string $originPath, array $options): string
    {
        return hash(
            'sha256',
            implode(':', [filesize($originPath) ?? 0, ...$options,])
        );
    }

    private function updateMetadata(array $metadata): void
    {
        $fingerprintCacheRoot = $this->fsPath($this->fingerprintCachePath) . '/';

        foreach ($metadata as $origin => $updMeta) {
            $metadataPath = $fingerprintCacheRoot . str_replace('/', '-', trim($origin, '/')) . '.metadata.json';
            $dirname = dirname($metadataPath);
            if (!is_dir($dirname)) {
                mkdir($dirname, 0775, true);
            }

            $fp = fopen($metadataPath, 'c+');
            flock($fp, LOCK_EX);

            $contents = stream_get_contents($fp);

            $existMeta = $contents
                ? json_decode($contents, true)
                : [];

            $sumMeta = array_merge($existMeta, $updMeta);

            ftruncate($fp, 0);
            rewind($fp);

            fwrite(
                $fp,
                json_encode($sumMeta, JSON_PRETTY_PRINT)
            );

            fflush($fp);

            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    private function isVariantUpToDate(
        string $source,
        string $destination,
        array $options
    ): bool {
        $sourceMtime = file_exists($source) ? filemtime($source) : 0;
        $destinationMtime = file_exists($destination) ? filemtime($destination) : 0;
        if (!$sourceMtime || !$destinationMtime || $destinationMtime < $sourceMtime) {
            return false;
        }

        // $fingerprintCacheRoot = $this->fsPath($this->fingerprintCachePath);


        return true;
    }
}
