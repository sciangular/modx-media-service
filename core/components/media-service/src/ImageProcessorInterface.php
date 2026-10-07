<?php
namespace Tsyfra\MediaService;

interface ImageProcessorInterface
{
  public function generate(
    string $source,
    string $destination,
    array $options
  ): array;
}
