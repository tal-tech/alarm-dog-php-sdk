<?php

declare(strict_types=1);

namespace Dog\Alarm\Receiver;

use Dog\Alarm\Exception\InvalidArgumentException;
use Dog\Alarm\Receiver\Channel\ChannelAbstract;

class Receiver
{
    /**
     * @var array
     */
    protected $alarmGroups = [];

    /**
     * @var ChannelAbstract[]
     */
    protected $channels = [];

    /**
     * @param array $alarmGroupIDs [1, 2]
     * @param array $channels [new ChannelAbstract, new ChannelAbstract]
     */
    public function __construct(array $alarmGroupIDs = [], array $channels = [])
    {
        if (! empty($alarmGroupIDs)) {
            $this->addAlarmGroups($alarmGroupIDs);
        }
        if (! empty($channels)) {
            $this->addChannels($channels);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * 添加告警通知组.
     *
     * @param int $groupID
     * @throws InvalidArgumentException
     * @return Receiver
     */
    public function addAlarmGroup($groupID)
    {
        $fmtGroupID = (int) $groupID;
        if ($fmtGroupID === 0) {
            throw new InvalidArgumentException(sprintf('alarm group ID must be integer but got %s', $groupID));
        }

        $this->alarmGroups[] = $fmtGroupID;

        return $this;
    }

    /**
     * 添加多个告警通知组.
     *
     * @param array $groupIDs [1, 2]
     * @param bool $replace
     * @throws InvalidArgumentException
     * @return Receiver
     */
    public function addAlarmGroups(array $groupIDs, $replace = false)
    {
        $filteredGroupIDs = [];
        foreach ($groupIDs as $groupID) {
            $fmtGroupID = (int) $groupID;
            if ($fmtGroupID === 0) {
                throw new InvalidArgumentException(sprintf('alarm group ID must be integer but got %s', $groupID));
            }

            $filteredGroupIDs[] = $fmtGroupID;
        }

        if ($replace) {
            $this->alarmGroups = $filteredGroupIDs;
        } else {
            $this->alarmGroups += $filteredGroupIDs;
        }

        return $this;
    }

    /**
     * 添加告警通知渠道.
     *
     * @param int $groupID
     * @throws InvalidArgumentException
     * @return Receiver
     */
    public function addChannel(ChannelAbstract $channel)
    {
        $this->channels[] = $channel;

        return $this;
    }

    /**
     * 添加多个告警通知渠道.
     *
     * @param array $channels [new ChannelAbstract, new ChannelAbstract]
     * @param bool $replace
     * @throws InvalidArgumentException
     * @return Receiver
     */
    public function addChannels(array $channels, $replace = false)
    {
        foreach ($channels as $channel) {
            if (! $channel instanceof ChannelAbstract) {
                throw new InvalidArgumentException(sprintf('channel must be instance of %s', ChannelAbstract::class));
            }
        }

        if ($replace) {
            $this->channels = $channels;
        } else {
            $this->channels += $channels;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [
            'alarmgroup' => $this->alarmGroups,
            'channels' => [],
        ];
        foreach ($this->channels as $channel) {
            /* @var ChannelAbstract $channel */
            $array['channels'][$channel->getChannel()] = $channel->getAttributes();
        }

        return $array;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
