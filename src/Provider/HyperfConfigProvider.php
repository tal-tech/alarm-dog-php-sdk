<?php

declare(strict_types=1);

namespace Dog\Alarm\Provider;

class HyperfConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                    ],
                ],
                'ignore_annotations' => [
                    'mixin',
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of xiaotianquan alarm client.',
                    'source' => __DIR__ . '/../../config/dog.php',
                    'destination' => BASE_PATH . '/config/autoload/dog.php',
                ],
            ],
        ];
    }
}
