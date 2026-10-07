<?php

declare(strict_types=1);

namespace Tsyfra\MediaService;

class ResponsiveImageManager
{
    private string $mediaBaseUrl;
    private string $mediaCacheUrl;
    private string $imageManifestPath;

    public function __construct(
        private readonly string $WEB_ROOT_PATH,
        private ImageProcessorInterface $processor,
        private readonly array $config
    ) {
        $this->mediaBaseUrl = $config['mediaBaseUrl'];
        $this->mediaCacheUrl = $config['mediaCacheUrl'];
        $this->imageManifestPath = $config['imageManifestPath'];
    }

    public function getResponsiveImage(array $imageSources, string $imageRole, array $attributes = []): array
    {
        $imageConfig = $this->config['imageRoles'][$imageRole] ?? [];
        if (!$imageConfig || !$imageSources) {
            return [];
        }
        $transformConfigBuilder = new ImageTransformConfigBuilder($this->WEB_ROOT_PATH, $this->config);
        $transformConfigMap = $transformConfigBuilder->buildConfigMap($imageSources, $imageRole);

        $manifestStore = new ImageManifestStore($this->WEB_ROOT_PATH . $this->imageManifestPath);
        foreach ($transformConfigMap as &$config) {
            $manifest = $manifestStore->resolveManifest($config);
            if ($manifest) {
                $config['manifest'] = $manifest;
                continue;
            }

            // process the image and generate the manifest payload
            $intrinsicSize = $this->processImage($config);

            $manifestPayload = [
                'intrinsicWidth' => $intrinsicSize['intrinsicWidth'] ?? 0,
                'intrinsicHeight' => $intrinsicSize['intrinsicHeight'] ?? 0,
            ];
            $config['manifest'] = $manifestStore->updateManifestRecord($manifestPayload, $config) ?? [];
        }
        unset($config);

        return $transformConfigMap;
    }

    private function processImage(array $config): array
    {
        $originPath = $config['sourcePath'] ?? '';
        $destinationPath = $config['destinationPath'] ?? '';
        $options = [
            'width' => $config['width'] ?? 0,
            'height' => $config['height'] ?? 0,
            'quality' => $config['quality'] ?? 80,
            'strip' => $config['strip'] ?? true,
            'format' => pathinfo($destinationPath, PATHINFO_EXTENSION),
        ];

        return $this->processor->generate($originPath, $destinationPath, $options);
    }
}
