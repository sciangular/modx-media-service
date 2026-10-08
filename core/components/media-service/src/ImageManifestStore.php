<?php

namespace Tsyfra\MediaService;

final class ImageManifestStore
{
    public function __construct(private readonly string $manifestPath) {}

    public function resolveManifest(array $config): array
    {
        $manifestPath = $this->getManifestPath($config['sourceUrl']);
        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifestMap = json_decode(file_get_contents($manifestPath), true);
        if (!$manifestMap || !isset($manifestMap[$config['destinationUrl']])) {
            return [];
        }

        $manifest = $manifestMap[$config['destinationUrl']];

        return $this->validateManifest($manifest, $config) ? $manifest : [];
    }

    private function getManifestPath(string $sourceUrl): string
    {
        return trim($this->manifestPath . '/' . $sourceUrl . '.manifest.json');
    }

    private function validateManifest(array $manifest, array $config): bool
    {
        return isset($manifest['fingerprint'])
            && $manifest['fingerprint'] === $this->makeFingerprint($config);
    }

    private function makeFingerprint(array $config): string
    {
        return hash(
            'sha256',
            implode(':', [
                $config['sourceUrl'] ?? '',
                $config['sourceMtime'] ?? 0,
                $config['sourceSize'] ?? 0,
                $config['width'] ?? 0,
                $config['height'] ?? 0,
                $config['quality'] ?? 0,
                $config['strip'] ?? 1,
                $config['watermark']['url'] ?? '',
                $config['watermark']['width'] ?? '',
                $config['watermark']['opacity'] ?? '',
                $config['watermark']['placement'] ?? '',
                $config['watermark']['margin'] ?? '',
            ])
        );
    }

    public function updateManifestRecord(array $manifestPayload, array $manifestConfig): array
    {
        $manifestPath = $this->getManifestPath($manifestConfig['sourceUrl']);
        $dirname = dirname($manifestPath);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0775, true);
        }

        $fp = fopen($manifestPath, 'c+');
        flock($fp, LOCK_EX);

        $contents = stream_get_contents($fp);
        $data = $contents
            ? json_decode($contents, true)
            : [];
        $key = $manifestConfig['destinationUrl'];

        $data[$key] = $manifestPayload ?? [];
        $data[$key]['fingerprint'] = $this->makeFingerprint($manifestConfig);

        ftruncate($fp, 0);
        rewind($fp);

        fwrite(
            $fp,
            json_encode($data, JSON_PRETTY_PRINT)
        );

        fflush($fp);

        flock($fp, LOCK_UN);
        fclose($fp);

        return $data[$key];
    }
}
