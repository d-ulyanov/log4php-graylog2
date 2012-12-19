log4php-amqp
============

Copyright (c) 2012 Dmitriy Ulyanov

Here you can find 2 new appenders for log4php: LoggerAppenderAMQP and LoggerAppenderGraylog2.<br />
You can pass log messages to Graylog2 or AMQP (RabbitMQ for ex.) using it.

Appender LoggerAppenderGraylog2 can pass messages directly to Graylog2 server.<br />
Appender LoggerAppenderAMQP can pass messages to AMQP Server. In this case you can set up yours graylog2 to recieving messages from AMQP.

If you would like to pass messages in GELF format, use special layout: LoggerLayoutGelf

-----------

Usage:

1. Set up your log4php config file (see exampleConfig.xml):
	
2. Use your new logger:

require 'log4php/Logger.php';<br />
require 'log4php/appenders/LoggerAppenderAMQP.php';<br />
require 'log4php/appenders/LoggerAppenderGraylog2.php';<br />
require 'log4php/layouts/LoggerLayoutGelf.php';<br />

Logger::configure('exampleConfig.xml', 'LoggerConfigurationAdapterXML');

$myLogger = Logger::getLogger('MyLogger');<br />
$myLogger->debug("Hello world!");

-----------