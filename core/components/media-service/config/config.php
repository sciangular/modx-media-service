<?php

return [
  'mediaBasePath' => '/assets/media',
  'mediaCachePath' => '/assets/media-cache',
  'fingerprintCachePath' => '/assets/media-cache/fingerprints',
  'artVariantRegistry' => [
    'l' => [
      'srcIndex' => 0,
      'quality' => 80,
      'strip' => true,
      'sizes' => [
        'xs' => ['width' => 192, 'height' => 144],
        'sm' => ['width' => 384, 'height' => 288],
        'md' => ['width' => 768, 'height' => 576],
        'lg' => ['width' => 1200, 'height' => 900],
        'xl' => ['width' => 1600, 'height' => 1200, 'quality' => 95],
        '2xl' => ['width' => 1920, 'height' => 1440, 'quality' => 95, 'strip' => false],
      ],
    ],
    'p' => [
      'srcIndex' => 1,
      'quality' => 80,
      'strip' => true,
      'sizes' => [
        'xs' => ['width' => 192, 'height' => 256],
        'sm' => ['width' => 384, 'height' => 512],
        'md' => ['width' => 768, 'height' => 1024],
        'lg' => ['width' => 1024, 'height' => 1366, 'quality' => 95],
      ],
    ],
    's' => [
      'srcIndex' => 2,
      'quality' => 80,
      'strip' => true,
      'sizes' => [
        'xs' => ['width' => 192, 'height' => 192],
        'sm' => ['width' => 384, 'height' => 384],
        'md' => ['width' => 768, 'height' => 768, 'quality' => 95],
      ],
    ],
  ],
  'mediaRegistry' => [
    'hero' => [
      'source' => [
        [
          'mediaAttr' => '(max-width: 639px)',
          'artVariant' => 'p',
          'srcsetSizes' => ['sm', 'md', 'lg'],
          'mimeType' => 'image/avif',
          'sizesAttr' => '100vw',
          'defaultSize' => 'md',
        ],
        [
          'mediaAttr' => '(min-width: 640px)',
          'artVariant' => 'l',
          'srcsetSizes' => ['md', 'lg', 'xl', '2xl'],
          'mimeType' => 'image/avif',
          'sizesAttr' => '100vw',
          'defaultSize' => 'md',
        ],
        [
          'mediaAttr' => '(max-width: 639px)',
          'artVariant' => 'p',
          'srcsetSizes' => ['sm', 'md', 'lg'],
          'mimeType' => 'image/webp',
          'sizesAttr' => '100vw',
          'defaultSize' => 'md',
        ],
        [
          'mediaAttr' => '(min-width: 640px)',
          'artVariant' => 'l',
          'srcsetSizes' => ['md', 'lg', 'xl', '2xl'],
          'mimeType' => 'image/webp',
          'sizesAttr' => '100vw',
          'defaultSize' => 'md',
        ],
      ],
      'img' => [
        'artVariant' => 'l',
        'srcSize' => 'lg',
        'srcsetSizes' => ['md', 'lg', 'xl', '2xl'],
        'mimeType' => 'image/webp',
        'sizesAttr' => '100vw',
      ],
    ],
    'post' => [
      'img' => [
        'artVariant' => 'l',
        'srcsetSizes' => ['sm', 'md', 'lg', 'xl'],
        'srcSize' => 'lg',
        'mimeType' => 'image/webp',
        'sizesAttr' => '(max-width: 640px) 100vw, 75vw',
      ],
    ],
    'card' => [
      'img' => [
        'artVariant' => 'l',
        'srcsetSizes' => ['xs', 'sm', 'md'],
        'srcSize' => 'md',
        'mimeType' => 'image/webp',
      ],
    ],
    'thumb' => [
      'img' => [
        'artVariant' => 's',
        'srcsetSizes' => ['xs', 'sm', 'md'],
        'srcSize' => 'sm',
        'mimeType' => 'image/webp',
      ],
    ],
  ]
];
