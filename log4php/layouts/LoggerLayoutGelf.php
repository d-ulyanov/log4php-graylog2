<?php
/**
 * This layout outputs events in a JSON-encoded Gelf format
 * @see http://graylog2.org/about/gelf
 *
 * Configurable parameters for this layout are:
 *
 * - <int> shortMessageLength
 * - <string> shortMessageEndTag
 * - <string> hostname
 *
 * Default hostname is result of gethostname()
 *
 * An example for this layout:
 *
 * Example of output:
 * <pre>
 * {
 *      "version":"1.0",
 *      "timestamp":1355753518.4781,
 *      "short_message":"Response success",
 *      "full_message":"Response success status: TRUE",
 *      "facility":"ApiLogger",
 *      "host":"test.com",
 *      "level":7,
 *      "file":"\/path\/to\/file.php",
 *      "line":70
 * }
 * </pre>
 *
 * @version 0.2
 * @author Dmitriy Ulyanov dmitriy.ulyanov@wikimart.ru
 * @license MIT
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */
class LoggerLayoutGelf extends LoggerLayout
{
    /**
     *  Log levels according to syslog priority
     */
    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    /**
     * Version of Graylog2 GELF
     */
    const GELF_PROTOCOL_VERSION = '1.0';

    /**
     * Tag for dividing short and full message
     */
    const SHORT_MESSAGE_END_TAG = "</shortMessage>";

    /** Maps log4php levels to equivalent Gelf levels */
    protected $_levelMap = array(
        LoggerLevel::TRACE => self::DEBUG,
        LoggerLevel::DEBUG => self::DEBUG,
        LoggerLevel::INFO => self::INFO,
        LoggerLevel::WARN => self::WARNING,
        LoggerLevel::ERROR => self::ERROR,
        LoggerLevel::FATAL => self::CRITICAL,
    );

    protected $_shortMessageLength = 70;
    protected $_hostname;
    protected $_shortMessageEndTag = self::SHORT_MESSAGE_END_TAG;

    public function activateOptions()
    {
        $this->setHostname(gethostname());
        return parent::activateOptions();
    }

    public function format(LoggerLoggingEvent $event)
    {
        $messageAsArray = array(
            'version' => self::GELF_PROTOCOL_VERSION,
            'timestamp' => $event->getTimeStamp(),
            'short_message' => $this->getShortMessage($event),
            'full_message' => $this->getFullMessage($event),
            'facility' => $event->getLoggerName(),
            'host' => $this->getHostname(),
            'level' => $this->getGELFLevel($event->getLevel()),
            'file' => $event->getLocationInformation()->getFileName(),
            'line' => $event->getLocationInformation()->getLineNumber()
        );

        foreach ($event->getMDCMap() as $key => $value)
        {
            $messageAsArray['_MDC_'.$key] = $value;
        }

        return json_encode($messageAsArray);
    }

    protected function getShortMessage(LoggerLoggingEvent $event)
    {
        if (strpos($event->getMessage(), $this->getShortMessageEndTag()) !== false)
        {
            list($shortMessage) = explode($this->getShortMessageEndTag(), $event->getMessage());
            return $shortMessage;
        }
        return mb_substr($event->getMessage(), 0, $this->getShortMessageLength());
    }

    protected function getFullMessage(LoggerLoggingEvent $event)
    {
        if (strpos($event->getMessage(), $this->getShortMessageEndTag()) !== false)
        {
            list(, $fullMessage) = explode($this->getShortMessageEndTag(), $event->getMessage());
            return $fullMessage;
        }
        return $event->getMessage();
    }


    protected function getGELFLevel(LoggerLevel $level)
    {
        $int = $level->toInt();

        if (isset($this->_levelMap[$int])) {
            return $this->_levelMap[$int];
        } else {
            return self::DEBUG;
        }
    }

    public function setShortMessageLength($shortMessageLength)
    {
        $this->_shortMessageLength = $shortMessageLength;
    }

    public function getShortMessageLength()
    {
        return $this->_shortMessageLength;
    }

    public function setHostname($hostname)
    {
        $this->_hostname = $hostname;
    }

    public function getHostname()
    {
        return $this->_hostname;
    }

    public function setShortMessageEndTag($shortMessageEndTag)
    {
        $this->_shortMessageEndTag = $shortMessageEndTag;
    }

    public function getShortMessageEndTag()
    {
        return $this->_shortMessageEndTag;
    }
}