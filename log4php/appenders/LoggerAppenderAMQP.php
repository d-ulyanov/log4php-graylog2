<?php
/**
 * AMQPAppender appends log events to a AMQP.
 *
 * This appender uses a layout.
 *
 * Configurable parameters for this appender are:
 *
 * - <string> host                - AMQP host
 * - <int>    port                - AMQP port
 * - <string> vhost               - AMQP vhost
 * - <string> login               - AMQP login
 * - <string> password            - AMQP password
 * - <string> exchangeName        - AMQP exchange name
 * - <string> exchangeType        - AMQP exchange type (direct | fanout ). Default - direct
 * - <string> queueName           - AMQP queue name
 * - <string> routingKey          - AMQP routing key. Set up AMQP server to route messages with this key to your queue
 * - <int>    skipConnectionError - 1 by defalut. All connection errors will be skipped without any error or exception
 *
 * @version 0.2
 * @author Dmitriy Ulyanov dmitriy.ulyanov@wikimart.ru
 * @license MIT
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */
class LoggerAppenderAMQP extends LoggerAppender
{
    /**
     * AMQP connection settings
     */
    protected $_host;
    protected $_port;
    protected $_vhost;
    protected $_login;
    protected $_password;
    protected $_exchangeName;
    protected $_exchangeType = 'direct';
    protected $_queueName;
    protected $_routingKey;
    protected $_skipConnectionError = 1;

    protected static $_AMQPConnection;
    protected static $_AMQPExchange;

	/**
	 * Forwards the logging event to the AMQP.
	 * @param LoggerLoggingEvent $event
	 */
	protected function append(LoggerLoggingEvent $event)
	{
        try
        {
            $this->getAMQPExchange()->publish(
                $this->layout->format($event),
                $this->getRoutingKey(),
                AMQP_NOPARAM,
                array(
                    'content_type' => 'application/json',
                    'content_encoding' => 'UTF-8'
                )
            );
        }
        catch (Exception $e)
        {
            if (!$this->getSkipConnectionError())
            {
                throw $e;
            }
        }
	}

    protected function setAMQPConnection($AMQPConnection)
    {
        self::$_AMQPConnection = $AMQPConnection;
    }

    protected function getAMQPConnection()
    {
        if (is_null(self::$_AMQPConnection))
        {
            self::$_AMQPConnection = $this->createAMQPConnection();
        }
        return self::$_AMQPConnection;
    }

    protected function createAMQPConnection()
    {
        $connection = new AMQPConnection();
        $connection->setHost($this->getHost());
        $connection->setPort($this->getPort());
        $connection->setVhost($this->getVhost());
        $connection->setLogin($this->getLogin());
        $connection->setPassword($this->getPassword());
        $connection->connect();

        return $connection;
    }

    protected function setAMQPExchange($AMQPExchange)
    {
        self::$_AMQPExchange = $AMQPExchange;
    }

    protected function getAMQPExchange()
    {
        if (is_null(self::$_AMQPExchange))
        {
            $channel = new AMQPChannel($this->getAMQPConnection());
            $exchange = new AMQPExchange($channel);
            $exchange->setName($this->getExchangeName());
            $exchange->setType($this->getExchangeType());
            $exchange->setFlags(AMQP_DURABLE);
            $exchange->declare();

            $queue = new AMQPQueue($channel);
            $queue->setName($this->getQueueName());
            $queue->setFlags(AMQP_DURABLE);
            $queue->setArgument('x-ha-policy', 'all');
            $queue->declare();
            $queue->bind($this->getExchangeName(), $this->getRoutingKey());

            self::$_AMQPExchange = $exchange;
        }
        return self::$_AMQPExchange;
    }

    public function setRoutingKey($AMQPRoutingKey)
    {
        $this->_routingKey = $AMQPRoutingKey;
    }

    public function getRoutingKey()
    {
        return $this->_routingKey;
    }

    public function setHost($host)
    {
        $this->_host = $host;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setLogin($login)
    {
        $this->_login = $login;
    }

    public function getLogin()
    {
        return $this->_login;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPort($port)
    {
        $this->_port = $port;
    }

    public function getPort()
    {
        return $this->_port;
    }

    public function setVhost($vhost)
    {
        $this->_vhost = $vhost;
    }

    public function getVhost()
    {
        return $this->_vhost;
    }

    public function setSkipConnectionError($skipConnectionError)
    {
        $this->_skipConnectionError = $skipConnectionError;
    }

    public function getSkipConnectionError()
    {
        return $this->_skipConnectionError;
    }

    public function setExchangeName($exchange)
    {
        $this->_exchangeName = $exchange;
    }

    public function getExchangeName()
    {
        return $this->_exchangeName;
    }

    public function setQueueName($queue)
    {
        $this->_queueName = $queue;
    }

    public function getQueueName()
    {
        return $this->_queueName;
    }

    public function setExchangeType($exchangeType)
    {
        $this->_exchangeType = $exchangeType;
    }

    public function getExchangeType()
    {
        return $this->_exchangeType;
    }
}