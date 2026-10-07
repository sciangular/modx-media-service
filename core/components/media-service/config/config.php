<?php

return [
  'mediaBaseUrl' => '/assets/media',
  'mediaCacheUrl' => '/assets/media-cache',
  'imageManifestPath' => '/assets/media-cache/.manifest',
  'imagePresets' => [
    'l' => [
      'sourceIndex' => 0,
      'quality' => 80,
      'strip' => true,
      'sizes' => [
        'xs' => ['width' => 192, 'height' => 144],
        'sm' => ['width' => 384, 'height' => 288],
        'md' => ['width' => 768, 'height' => 576],
        'lg' => ['width' => 1200, 'height' => 900],
        'xl' => ['width' => 1600, 'height' => 1200, 'quality' => 95],
        '2xl' => ['width' => 1920, 'height' => 1440, 'quality' => 100, 'strip' => false],
      ],
    ],
    'p' => [
      'sourceIndex' => 1,
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
      'sourceIndex' => 2,
      'quality' => 80,
      'strip' => true,
      'sizes' => [
        'xs' => ['width' => 192, 'height' => 192],
        'sm' => ['width' => 384, 'height' => 384],
        'md' => ['width' => 768, 'height' => 768, 'quality' => 95],
      ],
    ],
    'wm' => [
      'sourceIndex' => 0,
      'quality' => 80,
      'strip' => true,
      'sizes' => [
        'sm' => ['width' => 384, 'height' => null],
        'md' => ['width' => 768, 'height' => null],
        'lg' => ['width' => 1200, 'height' => null, 'quality' => 95, 'strip' => false, 'watermark' => 'default'],
      ],
    ],
  ],
  'watermarkPresets' => [
    'default' => [
      'url' => 'logo.png',
      'width' => 80,
      'opacity' => 0.5,
      'position' => 'top-left',
      'margin' => 20,
    ]
  ],
  'imageRoles' => [
    'hero' => [
      'sources' => [
        [
          'mediaAttr' => '(max-width: 639px)',
          'imagePreset' => 'p',
          'srcsetSizes' => ['sm', 'md', 'lg'],
          'mimeType' => 'image/avif',
          'sizesAttr' => '100vw',
          'baseSize' => 'md',
        ],
        [
          'mediaAttr' => '(min-width: 640px)',
          'imagePreset' => 'l',
          'srcsetSizes' => ['md', 'lg', 'xl', '2xl'],
          'mimeType' => 'image/avif',
          'sizesAttr' => '100vw',
          'baseSize' => 'md',
        ],
        [
          'mediaAttr' => '(max-width: 639px)',
          'imagePreset' => 'p',
          'srcsetSizes' => ['sm', 'md', 'lg'],
          'mimeType' => 'image/webp',
          'sizesAttr' => '100vw',
          'baseSize' => 'md',
        ],
        [
          'mediaAttr' => '(min-width: 640px)',
          'imagePreset' => 'l',
          'srcsetSizes' => ['md', 'lg', 'xl', '2xl'],
          'mimeType' => 'image/webp',
          'sizesAttr' => '100vw',
          'baseSize' => 'md',
        ],
      ],
      'img' => [
        'imagePreset' => 'l',
        'baseSize' => 'lg',
        'srcsetSizes' => ['md', 'lg', 'xl', '2xl'],
        'mimeType' => 'image/webp',
        'sizesAttr' => '100vw',
      ],
    ],
    'post' => [
      'img' => [
        'imagePreset' => 'wm',
        'baseSize' => 'lg',
        'srcsetSizes' => ['sm', 'md', 'lg'],
        'mimeType' => 'image/webp',
        'sizesAttr' => '(max-width: 640px) 100vw, 75vw',
      ],
    ],
    'card' => [
      'img' => [
        'imagePreset' => 'l',
        'baseSize' => 'md',
        'srcsetSizes' => ['xs', 'sm', 'md'],
        'mimeType' => 'image/webp',
      ],
    ],
    'thumb' => [
      'img' => [
        'imagePreset' => 's',
        'baseSize' => 'sm',
        'srcsetSizes' => ['xs', 'sm', 'md'],
        'mimeType' => 'image/webp',
      ],
    ],
  ]
];
