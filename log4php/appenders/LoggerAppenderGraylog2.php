<?php
/**
 * Graylog2Appender appends log events to a Graylog2 server.
 *
 * This appender uses a layout.
 *
 * Configurable parameters for this appender are:
 *
 * - <string> host      - Graylog2 server host
 * - <int> port         - Graylog2 server port. Default - 12201
 * - <int> chunkSize    - 8152 by defalut. Message chunk max size in bytes
 *
 * @version 0.1
 * @author Dmitriy Ulyanov dmitriy.ulyanov@wikimart.ru
 * @license MIT
 * @license http://opensource.org/licenses/mit-license.php The MIT License
 */
class LoggerAppenderGraylog2 extends LoggerAppender
{
    protected $_host;
    protected $_port = 12201;
    protected $_chunkSize = 8152;

	/**
	 * Forwards the logging event to the Graylog2 server.
	 * @param LoggerLoggingEvent $event
	 */
	protected function append(LoggerLoggingEvent $event)
	{
        $message = gzcompress($this->layout->format($event));
        $socket = $this->getSocketConnection();

        if (strlen($message) > $this->getChunkSize())
        {
            // A unique id which consists of the microtime and a random value
            $messageId = uniqid();

            // Split the message into chunks
            $messageChunks = str_split($message, $this->getChunkSize());
            $messageChunksCount = count($messageChunks);

            // Send chunks to graylog server
            foreach($messageChunks as $messageChunkIndex => $messageChunk)
            {
                $bytesWritten = $this->writeMessageChunkToSocket(
                    $socket,
                    $messageId,
                    $messageChunk,
                    $messageChunkIndex,
                    $messageChunksCount
                );

                if(false === $bytesWritten)
                {
                    // Abort due to write error
                    return false;
                }
            }
        }
        else
        {
            // A single write is enough to get the message published
            if(false === $this->writeMessageToSocket($socket, $message))
            {
                // Abort due to write error
                return false;
            }
        }
	}

    protected function getSocketConnection()
    {
        if (!filter_var($this->getHost(), FILTER_VALIDATE_IP))
        {
            $host = gethostbyname($this->getHost());
        }
        else
        {
            $host = $this->getHost();
        }

        return stream_socket_client(sprintf('udp://%s:%d', $host, $this->getPort()));
    }

    /**
     * @param resource $socket
     * @param float $messageId
     * @param string $messageChunk
     * @param integer $messageChunkIndex
     * @param integer $messageChunksCount
     * @return integer|boolean
     */
    protected function writeMessageChunkToSocket($socket, $messageId, $messageChunk, $messageChunkIndex, $messageChunksCount)
    {
        return fwrite(
            $socket,
            pack('CC', 30, 15) . substr(md5($messageId, true), 0, 8) . pack('CC', $messageChunkIndex, $messageChunksCount) . $messageChunk
        );
    }

    /**
     * @param resource $socket
     * @param string $preparedMessage
     * @return integer|boolean
     */
    protected function writeMessageToSocket($socket, $preparedMessage)
    {
        return fwrite($socket, $preparedMessage);
    }

    public function setChunkSize($chunkSize)
    {
        $this->_chunkSize = $chunkSize;
    }

    public function getChunkSize()
    {
        return $this->_chunkSize;
    }

    public function setHost($host)
    {
        $this->_host = $host;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function setPort($port)
    {
        $this->_port = $port;
    }

    public function getPort()
    {
        return $this->_port;
    }
}