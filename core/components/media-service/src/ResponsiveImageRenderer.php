<?php

declare(strict_types=1);

namespace Tsyfra\MediaService;

class ResponsiveImageRenderer
{
  public function __construct(
    private array $config,
    private array $manifestMap,
    private array $attributes = []
  ) {}

  public function render(string $imageRole): string
  {
    $role = $this->config['imageRoles'][$imageRole] ?? [];

    $sourceOutput = '';
    foreach ($role['sources'] ?? [] as $sourceConfig) {
      $sourceOutput .= $this->makeSource($sourceConfig);
    }

    $imgOutput = '';
    if (!empty($role['img'])) {
      $presetKey = $role['img']['imagePreset'] ?? '';
      $mimeType = $role['img']['mimeType'] ?? '';
      $baseSize = $role['img']['baseSize'] ?? 0;
      $manifestKey = "{$presetKey}:{$baseSize}:{$mimeType}";
      $baseManifest = $this->manifestMap[$manifestKey] ?? [];

      $imgAttributes = [
        'srcset' => $this->makeSrcset($role['img']['srcsetSizes'] ?? [], $presetKey, $mimeType),
        'src' => $this->makeCacheUrl($baseManifest['destinationUrl'] ?? ''),
        'sizes' => $role['img']['sizesAttr'] ?? '',
        'width' => $baseManifest['intrinsicWidth'] ?? '',
        'height' => $baseManifest['intrinsicHeight'] ?? '',
      ];

      $imgOutput = '<img ' . $this->makeAttributesString(array_merge($imgAttributes, $this->attributes)) . '>';
    }

    return $sourceOutput ? sprintf('<picture>%s%s</picture>', $sourceOutput, $imgOutput) : $imgOutput;
  }

  private function makeSrcset(array $srcset, string $presetKey, string $mimeType): string
  {
    $output = [];

    foreach ($srcset as $sizeKey) {
      $manifest = $this->manifestMap["{$presetKey}:{$sizeKey}:{$mimeType}"] ?? [];
      $output[] = sprintf(
        '%s %sw',
        $this->makeCacheUrl($manifest['destinationUrl'] ?? ''),
        $manifest['intrinsicWidth'] ?? 0
      );
    }

    return implode(', ', $output);
  }

  private function makeSource(array $sourceConfig): string
  {
    $presetKey = $sourceConfig['imagePreset'] ?? '';
    $mimeType = $sourceConfig['mimeType'] ?? '';
    $baseSize = $sourceConfig['baseSize'] ?? 0;
    $manifestKey = "{$presetKey}:{$baseSize}:{$mimeType}";

    $sourceAttributes = [
      'media' => $sourceConfig['mediaAttr'] ?? '',
      'type' => $sourceConfig['mimeType'] ?? '',
      'srcset' => $this->makeSrcset($sourceConfig['srcsetSizes'] ?? [], $presetKey, $mimeType) ?? '',
      'sizes' => $sourceConfig['sizesAttr'] ?? '',
      'width' => $this->manifestMap[$manifestKey]['intrinsicWidth'] ?? '',
      'height' => $this->manifestMap[$manifestKey]['intrinsicHeight'] ?? '',
    ];

    return '<source ' . $this->makeAttributesString($sourceAttributes) . '>';
  }

  private function makeCacheUrl(string $relativeUrl): string
  {
    return $this->config['mediaCacheUrl'] . '/' . ltrim($relativeUrl, '/');
  }

  private function makeAttributesString(array $attributes): string
  {
    $output = [];

    foreach ($attributes as $key => $value) {
      $output[] = sprintf('%s="%s"', $key, htmlspecialchars((string)$value, ENT_QUOTES));
    }

    return implode(' ', $output);
  }
}
