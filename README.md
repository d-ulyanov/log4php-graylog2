log4php-amqp
============

Copyright (c) 2012 Dmitriy Ulyanov

This is appender and layout for log4php to use it with AMQP.
With this appender you can pass logs to Graylog2 server.

If you would like to pass messages in GELF format, use special layout: LoggerLayoutGelf

-----------

Usage:

1. Set up your log4php config file like this (see exampleConfig.xml):

    <appender name="MyAMQPAppender" class="LoggerAppenderAMQP">
        <param name="host" value="rabbitmq.lan" />
        <param name="port" value="5672" />
        <param name="vhost" value="my_vhost" />
        <param name="login" value="my_login" />
        <param name="password" value="my_password" />
        <param name="exchangeName" value="my_exchange" />
        <param name="queueName" value="my_queue" />
        <param name="routingKey" value="some_routing_key" />
        <param name="skipConnectionError" value="0" />
        <layout class="LoggerLayoutGelf" />
    </appender>
    <logger name="MyLogger">
        <level value="DEBUG" />
        <appender_ref ref="MyAMQPAppender" />
    </logger>
	
2. Use your new logger:

require 'log4php/Logger.php';
require 'log4php/appenders/LoggerAppenderAMQP.php';
require 'log4php/layouts/LoggerLayoutGelf.php';

Logger::configure('exampleConfig.xml', 'LoggerConfigurationAdapterXML');

$myLogger = Logger::getLogger('MyLogger');
$myLogger->debug("Hello world!");

-----------