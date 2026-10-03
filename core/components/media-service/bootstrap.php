<?php

declare(strict_types=1);

use Tsyfra\MediaService\ImageProcessor;
use Tsyfra\MediaService\ImagickImageProcessor;

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace  // ['name' => 'katana', 'path' => '.../core/components/katana/']
 */

try {
  $namespacePath = $namespace['path'];

  // Composer autoload
  $autoload = $namespacePath . 'vendor/autoload.php';
  if (is_file($autoload)) {
    require_once $autoload;
  }

  // Register services in the container;
  if (!$modx->services->has('modx')) {
    $modx->services->add('modx', $modx);
  }

  $definitions = [
    'imageProcessor' => function () {
      $config = require __DIR__ . '/config/config.php';
      return new ImageProcessor(
        MODX_BASE_PATH,
        new ImagickImageProcessor(),
        $config
      );
    },
  ];

  foreach ($definitions as $id => $factory) {
    $modx->services->add($id, $factory);
  }
} catch (\Throwable $t) {
  $modx->log(\xPDO\xPDO::LOG_LEVEL_ERROR, $t->getMessage());
}
