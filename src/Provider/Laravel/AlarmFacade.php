<?php

declare(strict_types=1);

namespace Dog\Alarm\Provider\Laravel;

use Dog\Alarm\Alarm;
use Dog\Alarm\Receiver\Receiver;
use Illuminate\Support\Facades\Facade;
use Psr\Http\Message\ResponseInterface;

/**
 * @method static Alarm setTaskid(int $taskid)
 * @method static int|null getTaskid()
 * @method static Alarm setToken(string $token)
 * @method static string|null getToken()
 * @method static Alarm setBaseUri(string $baseUri)
 * @method static string getBaseUri()
 * @method static Alarm setGuzzleConfig(array $config)
 * @method static array getGuzzleConfig()
 * @method static ResponseInterface report(array $content, ?int $noticeTime = null, ?int $level = null, ?Receiver $receiver = null)
 * @method static array resolveResponse(ResponseInterface $response)
 *
 * @see \Dog\Alarm\Alarm
 */
class AlarmFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Alarm::class;
    }
}
