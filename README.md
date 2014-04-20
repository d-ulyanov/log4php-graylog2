=====
About
=====
Copyright (c) 2012-2014 Dmitriy Ulyanov

Here you can find 2 new appenders for log4php: LoggerAppenderAMQP and LoggerAppenderGraylog2.
You can pass log messages to Graylog2 or AMQP (RabbitMQ for ex.) using it.

Appender LoggerAppenderGraylog2 can pass messages directly to Graylog2 server.
Appender LoggerAppenderAMQP can pass messages to AMQP Server. In this case you can set up yours graylog2 to recieving messages from AMQP.

If you would like to pass messages in GELF format, use special layout: LoggerLayoutGelf

============
Installation
============

******************
For composer users
******************

1. Add to your composer.json:<br/>
<pre>
    {
        "require": {
            "dulyanov/log4php-graylog2": ">=1.0.0"
        },
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/d-ulyanov/log4php-graylog2.git"
            }
        ]
    }
</pre>

2. Run composer.phar update

***************
For other users
***************

1. Set up your log4php config file (see exampleConfig.xml)
2. Use your new logger:

require 'log4php/Logger.php';<br />
require 'log4php-graylog2/src/main/php/bootstrap.php';

=============
Configuration
=============

***
XML
***

<pre>
        <configuration xmlns="http://logging.apache.org/log4php/">
            <appender name="MyAMQPAppender" class="LoggerAppenderAMQP">
                <param name="host" value="example.com" />
                <param name="port" value="5672" />
                <param name="vhost" value="/logs" />
                <param name="login" value="my_login" />
                <param name="password" value="my_secret_password" />
                <param name="exchangeName" value="my_exchange" />
                <param name="routingKey" value="php_application" />
                <param name="contentType" value="application/json" />
                <layout class="LoggerLayoutGelf" />
            </appender>
            <appender name="MyGraylog2Appender" class="LoggerAppenderGraylog2">
                <param name="host" value="192.168.1.123" />
                <param name="port" value="12201" />
                <layout class="LoggerLayoutGelf" />
            </appender>
            <root>
                <level value="DEBUG" />
                <appender_ref ref="MyAMQPAppender" />
                <appender_ref ref="MyGraylog2Appender" />
            </root>
        </configuration>
</pre>

***
PHP
***

<pre>
        array(
            'rootLogger' => array(
                'appenders' => array('MyAMQPAppender', 'MyGraylog2Appender')
            ),
            'appenders' => array(
                'MyAMQPAppender' => array(
                    'class' => 'LoggerAppenderAMQP',
                    'params' => array(
                        'host' => 'example.com',
                        'port' => 5672,
                        'vhost' => '/logs',
                        'login' => 'my_login',
                        'password' => 'my_secret_password',
                        'exchangeName' => 'my_exchange',
                        'routingKey' => 'php_application',
                        'contentType' => 'application/json'
                    ),
                    'layout' => array(
                        'class' => 'LoggerLayoutGelf'
                    )
                ),
                'MyGraylog2Appender' => array(
                    'class' => 'LoggerAppenderGraylog2',
                    'params' => array(
                        'host' => '192.168.1.123',
                        'port' => 12201
                    ),
                    'layout' => array(
                        'class' => 'LoggerLayoutGelf'
                    )
                ),
            ),
        );
</pre>

=====
Usage
=====

<pre>
Logger::configure('exampleConfig.xml', 'LoggerConfigurationAdapterXML');

$myLogger = Logger::getLogger('MyLogger');
$myLogger->debug("Hello world!");
</pre>
