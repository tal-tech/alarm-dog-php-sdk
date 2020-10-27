<?php

declare(strict_types=1);

namespace Dog\Alarm\Exception;

class AlarmException extends \Exception
{
    // 发送请求的过程中异常
    const ERROR_SENDING = 1001;

    // 状态码错误
    const ERROR_STATUS_CODE = 1002;

    // 响应内容格式错误
    const ERROR_BODY_INVALID = 1003;

    // json错误码
    const ERROR_JSON_CODE = 1004;

    /**
     * @var array
     */
    protected $context = [];

    public function __construct($message = '', $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
