<?php

declare(strict_types=1);

namespace Dog\Alarm\Receiver\Channel;

abstract class ChannelAbstract
{
    /**
     * @var string
     */
    protected $channel = 'channel';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
