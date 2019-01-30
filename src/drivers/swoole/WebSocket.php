<?php
/**
 * yiiplus/yii2-websocket
 *
 * @category  PHP
 * @package   Yii2
 * @copyright 2018-2019 YiiPlus Ltd
 * @license   https://github.com/yiiplus/yii2-websocket/licence.txt Apache 2.0
 * @link      http://www.yiiplus.com
 */

namespace yiiplus\websocket\swoole;

use yiiplus\websocket\cli\WebSocket as CliWebSocket;

/**
 * Swoole WebSocket 客户端组建类
 *
 * 使用WebSocket客户端，需要注册此类到components配置中：
 *
 * ```php
 *  'components' => [
 *      ...
 *      'webslocket' => [
 *          'class' => 'yiiplus\websocket\swoole\WebSocket',
 *          'host' => '127.0.0.1',
 *          'port' => '9501',
 *          'path' => '/',
 *          'origin' => null,
 *          'channels' => [],
 *      ],
 *      ...
 *  ],
 * ```
 *
 * @property string        $host           WebSocket服务端HOST，此参数必须在components配置中设置
 * @property integer       $port           WebSocket服务端端口号，此参数必须在components配置中设置
 * @property string        $path           WebSocket Request-URI，默认为'/'，可通过components配置设置此参数
 * @property string        $origin         string Header Origin，默认为null，可通过components配置设置此参数
 * @property mixed         $returnData     返回数据
 * @property mixed         $_key Websocket Sec-WebSocket-Key
 * @property swoole_client $_socket        WebSocket客户端
 * @property mixed         $_buffer        用于对`recv`方法获取服务器接受到的数据进行缓存
 * @property mixed         $_connected     链接的状态
 *
 * @author gengxiankun <gengxiankun@126.com>
 * @since 1.0.0
 */
class WebSocket extends CliWebSocket
{
    /**
     * @const string PHPWebSocket客户端版本号
     */
	const VERSION = '0.1.4';

    /**
     * @const integer 生成TOKEN的长度
     */
    const TOKEN_LENGHT = 16;

    /**
     * @var string WebSocket服务端HOST
     */
    public $host;

    /**
     * @var integer WebSocket服务端端口号
     */
    public $port;

    /**
     * @var string WebSocket Request-URI
     */
    public $path = '/';

    /**
     * @var string Header Origin
     */
    public $origin = null;

    /**
     * @var mixed 返回数据
     */
    public $returnData = false;

    /**
     * @var string command class name
     */
    public $commandClass = Command::class;

    /**
     * @var string Websocket Sec-WebSocket-Key
     * Sec-WebSocket-Key是客户端也就是浏览器或者其他终端随机生成一组16位的随机base64编码
     */
    private $_key;

    /**
     * @var swoole_client Swoole客户端
     */
    private $_socket;

    /**
     * @var mixed 用于对recv获取服务器接受到的数据进行缓存
     */
    private $_buffer = '';

    /**
     * @var bool 是否链接
     */
    private $_connected = false;

    /**
     * 对象初始化
     */
    public function init()
    {
    	parent::init();

        if (!isset($this->host)) {
            throw new InvalidParamException('Host parameter does not exist.');
        }

        if (!isset($this->port)) {
            throw new InvalidParamException('Port parameter does not exist.');
        }

    	$this->_key = $this->generateToken(self::TOKEN_LENGHT);
    }

    /**
     * 将客户端连接到服务器
     *
     * @return $this
     */
    protected function connect()
    {
        // 建立连接
        $this->_socket = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!$this->_socket->connect($this->host, $this->port)) {
            return false;
        }
        // 握手确认
        $this->_socket->send($this->createHeader());
        return $this->recv();
    }

    /**
     * 向服务器发送数据
     *
     * @param string $data   发送的数据
     * @param string $type   发送的类型
     * @param bool   $masked 是否设置掩码
     *
     * @return bool 是否发送成功的状态
     */
    public function send($data, $type = 'text', $masked = true)
    {
        $this->connect();
        
        switch($type)
        {
            case 'text':
                $_type = WEBSOCKET_OPCODE_TEXT;
                break;
            case 'binary':
            case 'bin':
                $_type = WEBSOCKET_OPCODE_BINARY;
                break;
            case 'ping':
                $_type = WEBSOCKET_OPCODE_PING;
                break;
            default:
                return false;
        }

        // 将WebSocket消息打包并发送
        return $this->_socket->send(\swoole_websocket_server::pack(json_encode($data), $_type, true, $masked));
    }

    /**
     * 为WebSocket客户端创建Header
     *
     * @return string
     */
    private function createHeader()
    {
        $host = $this->host;
        if ($host === '127.0.0.1' || $host === '0.0.0.0')
        {
            $host = 'localhost';
        }

        return "GET {$this->path} HTTP/1.1" . "\r\n" .
            "Origin: {$this->origin}" . "\r\n" .
            "Host: {$host}:{$this->port}" . "\r\n" .
            "Sec-WebSocket-Key: {$this->_key}" . "\r\n" .
            "User-Agent: PHPWebSocketClient/" . self::VERSION . "\r\n" .
            "Upgrade: websocket" . "\r\n" .
            "Connection: Upgrade" . "\r\n" .
            "Sec-WebSocket-Protocol: wamp" . "\r\n" .
            "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";
    }

    /**
     * 从服务器端接收数据
     *
     * @return mixed
     */
    public function recv()
    {
        $data = $this->_socket->recv();
        if ($data === false)
        {
            echo "Error: {$this->_socket->errMsg}";
            return false;
        }
        $this->_buffer .= $data;
        $recv_data = $this->parseData($this->_buffer);
        if ($recv_data)
        {
            $this->_buffer = '';
            return $recv_data;
        }
        else
        {
            return false;
        }
    }

    /**
     * 解析收到的数据
     *
     * @param $response 相应数据
     *
     * @return 
     */
    private function parseData($response)
    {
        if (!$this->_connected)
		{
            // 确认请求来自WebSocket
			$response = $this->parseIncomingRaw($response);
			if (isset($response['Sec-Websocket-Accept'])
				&& base64_encode(pack('H*', sha1($this->_key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))) === $response['Sec-Websocket-Accept']
			)
			{
				$this->_connected = true;
				return true;
			}
			else
			{
				throw new \Exception("error response key.");
			}
		}

        // 解析WebSocket数据帧，@link: https://wiki.swoole.com/wiki/page/798.html
        $frame = \swoole_websocket_server::unpack($response);
        if ($frame)
        {
            return $this->returnData ? $frame->data : $frame;
        }
        else
        {
            throw new \Exception("swoole_websocket_server::unpack failed.");
        }
    }

    /**
     * 解析传入数据
     *
     * @param $response 相应数据
     *
     * @return array 返回解析的数据
     */
    private function parseIncomingRaw($response)
    {
        $retval = array();
        $content = "";
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $response));
        foreach ($fields as $field)
        {
            if (preg_match('/([^:]+): (.+)/m', $field, $match))
            {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./',
                    function ($matches)
                    {
                        return strtoupper($matches[0]);
                    },
                    strtolower(trim($match[1])));
                if (isset($retval[$match[1]]))
                {
                    $retval[$match[1]] = array($retval[$match[1]], $match[2]);
                }
                else
                {
                    $retval[$match[1]] = trim($match[2]);
                }
            }
            else
            {
                if (preg_match('!HTTP/1\.\d (\d)* .!', $field))
                {
                    $retval["status"] = $field;
                }
                else
                {
                    $content .= $field . "\r\n";
                }
            }
        }
        $retval['content'] = $content;
        return $retval;
    }

    /**
     * 生成Token
     *
     * @param int $length 生成Token的长度，默认16位
     *
     * @return string 返回生成的Token值
     */
    private function generateToken($length)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // 生成一些随机字符:
        for ($i = 0; $i < $length; $i++)
        {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // 添加数字
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, self::TOKEN_LENGHT);
        return base64_encode($randomString);
    }
}
