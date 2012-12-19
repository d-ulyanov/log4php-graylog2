log4php-amqp
============

Copyright (c) 2012 Dmitriy Ulyanov

This is appender and layout for log4php to use it with AMQP.
With this appender you can pass logs to Graylog2 server.

If you would like to pass messages in GELF format, use special layout: LoggerLayoutGelf

-----------

Usage:

1. Set up your log4php config file (see exampleConfig.xml):
	
2. Use your new logger:

require 'log4php/Logger.php';
require 'log4php/appenders/LoggerAppenderAMQP.php';
require 'log4php/layouts/LoggerLayoutGelf.php';

Logger::configure('exampleConfig.xml', 'LoggerConfigurationAdapterXML');

$myLogger = Logger::getLogger('MyLogger');
$myLogger->debug("Hello world!");

-----------