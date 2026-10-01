<?php

return [
  'mediaBasePath' => '/assets/media/',
  'mediaCachePath' => '/assets/media-cache/',
  'mediaSizeMap' => [
    's-xs' => ['width' => 192, 'height' => 192],
    's-sm' => ['width' => 384, 'height' => 384],
    's-md' => ['width' => 768, 'height' => 768],

    'p-xs' => ['width' => 192, 'height' => 256],
    'p-sm' => ['width' => 384, 'height' => 512],
    'p-md' => ['width' => 768, 'height' => 1024],
    'p-lg' => ['width' => 1024, 'height' => 1366],

    'l-xs' => ['width' => 192, 'height' => 144],
    'l-sm' => ['width' => 384, 'height' => 288],
    'l-md' => ['width' => 768, 'height' => 576],
    'l-lg' => ['width' => 1200, 'height' => 900],
    'l-xl' => ['width' => 1600, 'height' => 1200],
    'l-2xl' => ['width' => 1920, 'height' => 1440],
  ],
  'mediaRegistry' => [
    'hero' => [
      'source' => [
        [
          'media' => '(max-width: 639px)',
          'type' => 'image/avif',
          'srcset' => ['p-sm', 'p-md', 'p-lg'],
          'sizes' => '100vw',
          'dimensions' => 'p-md',
        ],
        [
          'media' => '(min-width: 640px)',
          'type' => 'image/avif',
          'srcset' => ['l-md', 'l-lg', 'l-xl', 'l-2xl'],
          'sizes' => '100vw',
          'dimensions' => 'l-md',
        ],
        [
          'media' => '(max-width: 639px)',
          'type' => 'image/webp',
          'srcset' => ['p-sm', 'p-md', 'p-lg'],
          'sizes' => '100vw',
          'dimensions' => 'p-md',
        ],
        [
          'media' => '(min-width: 640px)',
          'type' => 'image/webp',
          'srcset' => ['l-md', 'l-lg', 'l-xl', 'l-2xl'],
          'sizes' => '100vw',
          'dimensions' => 'l-md',
        ],
      ],
      'img' => [
        'type' => 'image/webp',
        'src' => 'l-lg',
        'srcset' => ['l-md', 'l-lg', 'l-xl', 'l-2xl'],
        'sizes' => '100vw',
      ],
    ],
    'post' => [
      'img' => [
        'type' => 'image/webp',
        'src' => 'l-lg',
        'srcset' => ['l-sm', 'l-md', 'l-lg', 'l-xl'],
        'sizes' => '(max-width: 640px) 100vw, 75vw',
      ],
    ],
    'card' => [
      'img' => [
        'type' => 'image/webp',
        'src' => 'l-md',
        'srcset' => ['l-xs', 'l-sm', 'l-md'],
      ],
    ],
    'thumb' => [
      'img' => [
        'type' => 'image/webp',
        'src' => 's-sm',
        'srcset' => ['s-xs', 's-sm', 's-md'],
      ],
    ],
  ]
];
