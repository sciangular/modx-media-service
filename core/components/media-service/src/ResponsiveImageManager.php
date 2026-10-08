<?php

declare(strict_types=1);

namespace Tsyfra\MediaService;

final class ResponsiveImageManager
{
    private string $mediaBaseUrl;
    private string $imageManifestPath;

    public function __construct(
        private readonly string $WEB_ROOT_PATH,
        private ImageProcessorInterface $processor,
        private readonly array $config
    ) {
        $this->mediaBaseUrl = $config['mediaBaseUrl'];
        $this->imageManifestPath = $config['imageManifestPath'];
    }

    public function getResponsiveImage(array $imageSources, string $imageRole, array $attributes = []): string
    {
        $imageConfig = $this->config['imageRoles'][$imageRole] ?? [];
        if (!$imageConfig || !$imageSources) {
            return '';
        }
        $transformConfigBuilder = new ImageTransformConfigBuilder($this->WEB_ROOT_PATH, $this->config);
        $transformConfigMap = $transformConfigBuilder->buildConfigMap($imageSources, $imageRole);

        $manifestMap = [];
        $manifestStore = new ImageManifestStore($this->WEB_ROOT_PATH . $this->imageManifestPath);

        foreach ($transformConfigMap as $key => $config) {
            $manifest = $manifestStore->resolveManifest($config);

            if (!$manifest) {
                $intrinsicSize = $this->processImage($config);
                $manifestPayload = [
                    'intrinsicWidth' => $intrinsicSize['intrinsicWidth'] ?? 0,
                    'intrinsicHeight' => $intrinsicSize['intrinsicHeight'] ?? 0,
                ];
                $manifest = $manifestStore->updateManifestRecord($manifestPayload, $config) ?? [];
            }

            $manifest['destinationUrl'] = $config['destinationUrl'] ?? '';
            $manifestMap[$key] = $manifest;
        }

        $renderer = new ResponsiveImageRenderer($this->config, $manifestMap, $attributes);

        return $renderer->render($imageRole);
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
        if (isset($config['watermark'])) {
            $options['watermark'] = $config['watermark'];
            $options['watermark']['path'] = $this->WEB_ROOT_PATH . $this->mediaBaseUrl . '/' . trim($options['watermark']['url']);
        }

        return $this->processor->generate($originPath, $destinationPath, $options);
    }
}
