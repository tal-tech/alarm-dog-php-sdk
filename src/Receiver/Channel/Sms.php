<?php

declare(strict_types=1);

namespace Dog\Alarm\Receiver\Channel;

use Dog\Alarm\Exception\InvalidArgumentException;

/**
 * 短信通知.
 */
class Sms extends ChannelAbstract
{
    /**
     * @var string
     */
    protected $channel = 'sms';

    /**
     * @param array $uids [98664, 98665]
     */
    public function __construct(array $uids = [])
    {
        if (! empty($uids)) {
            $this->addUids($uids);
        }
    }

    /**
     * 添加用户ID.
     *
     * @param int $uid
     * @throws InvalidArgumentException
     * @return Sms
     */
    public function addUid($uid)
    {
        $fmtUid = (int) $uid;
        if ($fmtUid === 0) {
            throw new InvalidArgumentException(sprintf('field `uid` must be integer but got %s', $uid));
        }

        $this->attributes[] = $fmtUid;

        return $this;
    }

    /**
     * 添加多个用户ID.
     *
     * @param array $uids [98664, 98665]
     * @param bool $replace
     * @throws InvalidArgumentException
     * @return Sms
     */
    public function addUids(array $uids, $replace = false)
    {
        $filteredUids = [];
        foreach ($uids as $uid) {
            $fmtUid = (int) $uid;
            if ($fmtUid === 0) {
                throw new InvalidArgumentException(sprintf('uid must be integer but got %s', $uid));
            }

            $filteredUids[] = $fmtUid;
        }

        if ($replace) {
            $this->attributes = $filteredUids;
        } else {
            $this->attributes += $filteredUids;
        }

        return $this;
    }
}
