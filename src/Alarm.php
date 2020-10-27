<?php

declare(strict_types=1);

namespace Dog\Alarm;

use Dog\Alarm\Exception\AlarmException;
use Dog\Alarm\Exception\InvalidArgumentException;
use Dog\Alarm\Receiver\Receiver;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Alarm
{
    /**
     * 告警级别.
     */
    // 通知
    const LEVEL_NOTICE = 0;

    // 警告
    const LEVEL_WARNING = 1;

    // 错误
    const LEVEL_ERROR = 2;

    // 紧急
    const LEVEL_EMERGENCY = 3;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * 告警任务ID.
     *
     * @var int
     */
    protected $taskid;

    /**
     * 告警任务token.
     *
     * @var string
     */
    protected $token;

    /**
     * 告警baseUri.
     *
     * @var string
     */
    protected $baseUri = 'http://alarm-dog-service.domain.com';

    /**
     * Guzzle配置.
     *
     * @var array
     */
    protected $guzzleConfig = [];

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;

        // 自动适配配置文件读取，并设置配置，此处配置不符合规范不抛出错误，保留可手动配置taskid、token的能力
        if (ConfigAdapter::canAdapt()) {
            $this->taskid = ConfigAdapter::get('dog.taskid');
            $this->token = ConfigAdapter::get('dog.token');
            if ($baseUri = ConfigAdapter::get('dog.base_uri')) {
                $this->setBaseUri($baseUri);
            }
            if ($guzzleConfig = ConfigAdapter::get('dog.guzzle', [])) {
                $this->setGuzzleConfig($guzzleConfig);
            }
        }

        // 初始化guzzle对象
        $this->initGuzzle();
    }

    /**
     * 设置taskid.
     *
     * @param int $taskid
     * @return Alarm
     */
    public function setTaskid($taskid)
    {
        $fmtTaskid = (int) $taskid;
        if ($fmtTaskid === 0) {
            throw new InvalidArgumentException('the configure item `taskid` must be integer');
        }

        $this->taskid = $taskid;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTaskid()
    {
        return $this->taskid;
    }

    /**
     * 设置token.
     *
     * @param string $token
     * @return Alarm
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 设置告警baseUri.
     *
     * @param string $baseUri
     * @return Alarm
     */
    public function setBaseUri($baseUri)
    {
        if (! filter_var($baseUri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                sprintf('config `base_uri` must be a active url, but got %s', $baseUri)
            );
        }

        // 去除最后的/
        if ($baseUri[mb_strlen($baseUri) - 1] === '/') {
            $baseUri = mb_substr($baseUri, 0, -1);
        }

        $this->baseUri = $baseUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * 设置guzzle的配置.
     *
     * @param array $config 格式请参考config/dog.php中guzzle下面的数组
     * @return Alarm
     */
    public function setGuzzleConfig(array $config)
    {
        $this->guzzleConfig = $config;

        return $this;
    }

    /**
     * @return array
     */
    public function getGuzzleConfig()
    {
        return $this->guzzleConfig;
    }

    /**
     * 发送告警.
     *
     * @param array $content 告警内容，必须为关联数组（JSON对象）
     * @param int $noticeTime 通知时间，默认当前
     * @param int $level 告警级别，默认通知级别
     * @param Receiver $receiver 自定义告警接收人
     * @throws InvalidArgumentException
     * @throws AlarmException
     * @return ResponseInterface
     */
    public function report(
        array $content,
        $noticeTime = null,
        $level = null,
        Receiver $receiver = null
    ) {
        if (is_null($this->taskid)) {
            throw new InvalidArgumentException('please set configure items `taskid` and `token`');
        }

        $this->validContent($content);

        // 参数组装
        [$timestamp, $sign] = $this->genSign();
        $json = [
            'taskid' => $this->taskid,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'ctn' => $content,
        ];
        if (! is_null($noticeTime)) {
            $json['notice_time'] = $noticeTime;
        }
        if (! is_null($level)) {
            $json['level'] = $level;
        }
        if (! is_null($receiver)) {
            $json['receiver'] = $receiver->toArray();
        }

        try {
            // 发送请求
            $uri = sprintf('%s/alarm/report', $this->baseUri);
            return $this->guzzle->post($uri, [
                'json' => $json,
            ]);
        } catch (Throwable $e) {
            throw new AlarmException(
                sprintf('send alarm failed: occured error on sending: %s', $e->getMessage()),
                AlarmException::ERROR_SENDING,
                $e
            );
        }
    }

    /**
     * 测试告警发送
     *
     * @throws InvalidArgumentException
     * @throws AlarmException
     * @return array
     */
    public function test()
    {
        if (is_null($this->taskid)) {
            throw new InvalidArgumentException('please set configure items `taskid` and `token`');
        }
        // 参数组装
        [$timestamp, $sign] = $this->genSign();
        $json = [
            'taskid' => $this->taskid,
            'timestamp' => $timestamp,
            'sign' => $sign,
        ];

        try {
            // 发送请求
            $uri = sprintf('%s/alarm/test', $this->baseUri);
            $response = $this->guzzle->post($uri, [
                'json' => $json,
            ]);

            return $this->resolveResponse($response);
        } catch (AlarmException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AlarmException(
                sprintf('test alarm failed: occured error on sending: %s', $e->getMessage()),
                AlarmException::ERROR_SENDING,
                $e
            );
        }
    }

    /**
     * 解析response.
     *
     * @throws AlarmException
     * @return array
     */
    public function resolveResponse(ResponseInterface $response)
    {
        // 状态码校验
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200) {
            throw new AlarmException(
                sprintf('send alarm failed: response status code not 200 but got %s', $statusCode),
                AlarmException::ERROR_STATUS_CODE,
                null,
                [
                    'status_code' => $statusCode,
                    'response' => $response,
                ]
            );
        }

        // 响应内容格式校验
        $body = (string) $response->getBody()->getContents();
        $json = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($json) || ! isset($json['code'])) {
            throw new AlarmException(
                sprintf('send alarm failed: response data is not a invalid json, body: %s', mb_substr($body, 0, 500)),
                AlarmException::ERROR_BODY_INVALID,
                null,
                [
                    'body' => $body,
                    'response' => $response,
                ]
            );
        }

        // 错误码校验
        if ($json['code'] !== 0) {
            throw new AlarmException(
                sprintf('send alarm failed: response error: %s', $json['msg']),
                AlarmException::ERROR_JSON_CODE,
                null,
                [
                    'json' => $json,
                ]
            );
        }

        return $json;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function validContent(array $content)
    {
        if (array_keys($content) === range(0, count($content) - 1)) {
            throw new InvalidArgumentException(
                'field `content` must be a JSON Object, please visit ' .
                'https://tal-tech.github.io/alarm-dog-docs/faq/why-json-object.html for more information'
            );
        }
    }

    /**
     * 生成签名.
     *
     * @param null|mixed $timestamp
     * @return array
     */
    protected function genSign($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        $sign = md5(sprintf('%s&%s%s', $this->taskid, $timestamp, $this->token));

        return [$timestamp, $sign];
    }

    /**
     * 初始化guzzle对象
     */
    protected function initGuzzle()
    {
        $guzzleCreator = new GuzzleCreator($this->container);
        $this->guzzle = $guzzleCreator->create($this->getGuzzleConfig());
    }
}
