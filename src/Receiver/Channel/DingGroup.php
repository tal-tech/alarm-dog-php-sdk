<?php

declare(strict_types=1);

namespace Dog\Alarm\Receiver\Channel;

use Dog\Alarm\Exception\InvalidArgumentException;

/**
 * 钉钉机器人.
 */
class DingGroup extends ChannelAbstract
{
    /**
     * @var string
     */
    protected $channel = 'dinggroup';

    /**
     * @param array $robots
     *                      [
     *                      ['webhook' => 'WEBHOOK', 'secret' => 'SECRET'],
     *                      ['webhook' => 'WEBHOOK', 'secret' => 'SECRET'],
     *                      ]
     */
    public function __construct(array $robots = [])
    {
        if (! empty($robots)) {
            $this->addRobots($robots);
        }
    }

    /**
     * 添加机器人.
     *
     * @param string $webhook
     * @param string $secret
     * @return DingGroup
     */
    public function addRobot($webhook, $secret)
    {
        $robot = [
            'webhook' => $webhook,
            'secret' => $secret,
        ];
        $this->attributes[] = $robot;

        return $this;
    }

    /**
     * 添加多个机器人.
     *
     * @param array $robots
     *                      [
     *                      ['webhook' => 'WEBHOOK', 'secret' => 'SECRET'],
     *                      ['webhook' => 'WEBHOOK', 'secret' => 'SECRET'],
     *                      ]
     * @param bool $replace 是否替换之前的机器人
     * @throws InvalidArgumentException
     * @return DingGroup
     */
    public function addRobots(array $robots, $replace = false)
    {
        // 校验参数
        $filteredRobots = [];
        foreach ($robots as $robot) {
            if (! array_key_exists('webhook', $robot)) {
                throw new InvalidArgumentException('key `webhook` is required');
            }
            if (! is_string($robot['webhook'])) {
                throw new InvalidArgumentException('the value of the key `webhook` must be string');
            }
            if (! array_key_exists('secret', $robot)) {
                throw new InvalidArgumentException('key `secret` is required');
            }
            if (! is_string($robot['secret'])) {
                throw new InvalidArgumentException('the value of the key `secret` must be string');
            }

            $filteredRobots[] = [
                'webhook' => $robot['webhook'],
                'secret' => $robot['secret'],
            ];
        }

        if ($replace) {
            $this->attributes = $filteredRobots;
        } else {
            $this->attributes += $filteredRobots;
        }

        return $this;
    }
}
