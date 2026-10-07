<?php

namespace Tsyfra\MediaService;

class ImageTransformConfigBuilder
{
  private string $mediaBaseUrl;
  private string $mediaCacheUrl;
  private static array $MIME_EXT_MAP = [
    'image/avif' => 'avif',
    'image/webp' => 'webp',
    'image/png' => 'png',
    'image/jpeg' => 'jpg',
    'image/gif' => 'gif',
  ];

  public function __construct(
    private readonly string $WEB_ROOT_PATH,
    private array $config
  ) {
    $this->mediaBaseUrl = $config['mediaBaseUrl'];
    $this->mediaCacheUrl = $config['mediaCacheUrl'];
  }

  public function buildConfigMap(array $imageSources, string $imageRole): array
  {
    $imageRole = $this->config['imageRoles'][$imageRole] ?? [];
    $transformConfigMap = [];

    foreach ($imageRole['sources'] ?? [] as $sourceConfig) {
      $presetKey = $sourceConfig['imagePreset'] ?? '';
      $mimeType = $sourceConfig['mimeType'] ?? '';

      foreach ($sourceConfig['srcsetSizes'] ?? [] as $sizeKey) {
        $key = "{$presetKey}:{$sizeKey}:{$mimeType}";
        if (isset($transformConfigMap[$key])) {
          continue;
        }

        $transformConfigMap[$key] = $this->buildConfig($imageSources, $presetKey, $sizeKey, $mimeType);
      }
    }

    if (isset($imageRole['img'])) {
      $presetKey = $imageRole['img']['imagePreset'] ?? '';
      $mimeType = $imageRole['img']['mimeType'] ?? '';
      $srcsizes = [
        ...$imageRole['img']['srcsetSizes'] ?? [],
        ...isset($imageRole['img']['baseSize']) ? [$imageRole['img']['baseSize']] : [],
      ];

      foreach ($srcsizes as $sizeKey) {
        $key = "{$presetKey}:{$sizeKey}:{$mimeType}";
        if (isset($transformConfigMap[$key])) {
          continue;
        }

        $transformConfigMap[$key] = $this->buildConfig($imageSources, $presetKey, $sizeKey, $mimeType);
      }
    }

    return $transformConfigMap;
  }

  private function buildConfig(array $imageSources, string $presetKey, string $sizeKey, string $mimeType): array
  {
    $imagePreset = $this->config['imagePresets'][$presetKey] ?? null;
    $sourceUrl = ltrim($imageSources[$imagePreset['sourceIndex']] ?? $imageSources[0], '/');
    $sourcePath = $this->WEB_ROOT_PATH . $this->mediaBaseUrl . '/' . $sourceUrl;
    $sourceMtime = filemtime($sourcePath) ?? 0;
    $sourceSize = filesize($sourcePath) ?? 0;
    $destinationSrc = $imageSources[0];
    $destinationUrl = ltrim(
      pathinfo($destinationSrc, PATHINFO_DIRNAME) . '/'
        . pathinfo($destinationSrc, PATHINFO_FILENAME)
        . "-{$presetKey}-{$sizeKey}."
        . self::$MIME_EXT_MAP[$mimeType],
      './'
    );
    $destinationPath = $this->WEB_ROOT_PATH . $this->mediaCacheUrl . '/' . $destinationUrl;
    $watermarkKey = $imagePreset['sizes'][$sizeKey]['watermark'] ?? '';
    $watermark = $this->config['watermarkPresets'][$watermarkKey] ?? null;
    if ($watermark) {
      $watermark['path'] = $this->WEB_ROOT_PATH . $this->mediaBaseUrl . '/' . trim($watermark['url'], '/');
    }

    return [
      'presetKey' => $presetKey,
      'sizeKey' => $sizeKey,
      'mimeType' => $mimeType,
      'sourceUrl' => $sourceUrl,
      'sourcePath' => $sourcePath,
      'sourceMtime' => $sourceMtime,
      'sourceSize' => $sourceSize,
      'destinationUrl' => $destinationUrl,
      'destinationPath' => $destinationPath,
      'width' => $imagePreset['sizes'][$sizeKey]['width'] ?? null,
      'height' => $imagePreset['sizes'][$sizeKey]['height'] ?? null,
      'quality' => $imagePreset['sizes'][$sizeKey]['quality'] ?? $imagePreset['quality'] ?? 75,
      'strip' => $imagePreset['sizes'][$sizeKey]['strip'] ?? $imagePreset['strip'] ?? true,
      'watermark' => $watermark,
    ];
  }
}
