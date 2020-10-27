<?php

declare(strict_types=1);

namespace Dog\Alarm\Receiver\Channel;

use Dog\Alarm\Exception\InvalidArgumentException;

/**
 * Webhook回调通知.
 */
class Webhook extends ChannelAbstract
{
    /**
     * @var string
     */
    protected $channel = 'webhook';

    /**
     * @param string $webhook http://yourdomain.com/callback
     */
    public function __construct($webhook = null)
    {
        if (! is_null($webhook)) {
            $this->setWebhook($webhook);
        }
    }

    /**
     * 设置webhook.
     *
     * @param string $webhook http://yourdomain.com/callback
     * @throws InvalidArgumentException
     * @return Webhook
     */
    public function setWebhook($webhook)
    {
        if (! filter_var($webhook, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf('webhook `%s` must be a active url', $webhook));
        }
        if (! (strpos($webhook, 'http://') === 0 || strpos($webhook, 'https://') === 0)) {
            throw new InvalidArgumentException(
                sprintf('webhook `%s` must be string begining with http:// or https://', $webhook)
            );
        }

        $this->attributes = $webhook;

        return $this;
    }
}
