<?php

/**
 * Media Service Snippet
 * @var MODX\Revolution\modX $modx
 */

$sourcesList = explode('||', $scriptProperties['sources'] ?? '');
$role = $scriptProperties['role'] ?? '';
$attributes = $scriptProperties['attributes'] ?? '';

if (!$sourcesList || !$role) {
  return;
}

$attributesList = [];
foreach (explode('||', $attributes) as $attribute) {
  [$key, $value] = explode('==', $attribute, 2);
  $attributesList[trim($key)] = trim($value);
}

$rim = $modx->services->get('ResponsiveImageManager');

return $rim->getResponsiveImage(
  $sourcesList,
  $role,
  $attributesList
);
