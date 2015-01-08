<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\DiffProvider\Binlog;

use ONGR\ConnectionsBundle\Entity\SyncJob;

/**
 * Parses binary log data (Interpreter).
 */
class BinlogParser implements \Iterator
{
    /**
     * Buffer isn't complete, load from the pipe.
     */
    const STATE_CLEAN = 0;

    /**
     * Buffer is filled, load from buffer.
     */
    const STATE_DIRTY = 1;

    // Returned array keys.
    const PARAM_QUERY = 0;
    const PARAM_DATE = 1;
    const PARAM_POSITION = 2;

    // Binlog line types.
    const LINE_TYPE_ANY = 0;
    const LINE_TYPE_UNKNOWN = 1;
    const LINE_TYPE_QUERY = 2;
    const LINE_TYPE_META = 3;
    const LINE_TYPE_PARAM = 4;
    const LINE_TYPE_ERROR = 5;

    // SQL statement types.
    const STATEMENT_TYPE_WHERE = 'WHERE';
    const STATEMENT_TYPE_SET = 'SET';

    // Bin log start type.
    const START_TYPE_NONE = 0;
    const START_TYPE_DATE = 1;
    const START_TYPE_POSITION = 2;

    /**
     * @var string Directory with log-dir files.
     */
    private $logDir;

    /**
     * @var string Base name of sql files.
     */
    private $baseName;

    /**
     * @var \DateTime|int
     */
    private $from;

    /**
     * @var int
     */
    private $startType;

    /**
     * @var resource Pipe read from.
     */
    private $pipe;

    /**
     * @var array
     */
    private $buffer;

    /**
     * @var int
     */
    private $key;

    /**
     * @var string
     */
    private $lastLine = null;

    /**
     * @var int
     */
    private $lastLineType;

    /**
     * @var \DateTime
     */
    private $lastDateTime;

    /**
     * @var int
     */
    private $lastLogPosition;

    /**
     * @var int Status of cache.
     */
    private $status = self::STATE_CLEAN;

    /**
     * @param string        $logDir    Directory with binary log files.
     * @param string        $baseName  Base name of bin log files.
     * @param \DateTime|int $from      Date or last position from which logs will be parsed.
     * @param int           $startType Specifies whether parse logs from date or last position.
     */
    public function __construct($logDir, $baseName, $from = null, $startType = self::START_TYPE_NONE)
    {
        $this->logDir = $logDir;
        $this->baseName = $baseName;
        $this->from = $from;
        $this->startType = $startType;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->key++;

        if ($this->status === self::STATE_CLEAN) {
            $this->nextBufferLine();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->current();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->buffer[$this->key];
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->key = 0;

        if ($this->pipe) {
            pclose($this->pipe);
        }

        $this->pipe = null;
        $this->lastLine = null;
        $this->lastLineType = null;
        $this->lastDateTime = null;
        $this->lastLogPosition = null;

        if ($this->status === self::STATE_CLEAN) {
            if (!empty($this->buffer)) {
                $this->status = self::STATE_DIRTY;
            } else {
                $this->nextBufferLine();
            }
        }
    }

    /**
     * Parse query line and store it to buffer.
     */
    protected function nextBufferLine()
    {
        $query = $this->parseQuery();

        if (!empty($query)) {
            $this->buffer[$this->key][self::PARAM_QUERY] = $query;
        } else {
            $this->buffer[$this->key] = false;
        }
    }

    /**
     * Retrieves a query from the binlog.
     *
     * @return null|array
     */
    protected function parseQuery()
    {
        if (empty($this->lastLine) || $this->lastLineType != self::LINE_TYPE_QUERY) {
            $this->getNextLine(self::LINE_TYPE_QUERY);

            if (empty($this->lastLine)) {
                return false;
            }
        }

        $buffer = $this->handleStart($this->lastLine);

        // Associate last date with current query.
        $this->buffer[$this->key][self::PARAM_DATE] = $this->lastDateTime;

        // Associate last log position with current query.
        $this->buffer[$this->key][self::PARAM_POSITION] = $this->lastLogPosition;

        $this->getNextLine(self::LINE_TYPE_QUERY);

        if ($buffer['type'] == SyncJob::TYPE_DELETE || $buffer['type'] === SyncJob::TYPE_UPDATE) {
            $buffer['where'] = $this->handleStatement($this->lastLine, self::STATEMENT_TYPE_WHERE);
        }

        if ($buffer['type'] == SyncJob::TYPE_CREATE || $buffer['type'] === SyncJob::TYPE_UPDATE) {
            $buffer['set'] = $this->handleStatement($this->lastLine, self::STATEMENT_TYPE_SET);
        }

        return $buffer;
    }

    /**
     * Read one line from pipe.
     *
     * @return string
     */
    protected function getLine()
    {
        $this->lastLine = fgets($this->getPipe());

        return $this->lastLine;
    }

    /**
     * @param int $type
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getNextLine($type = self::LINE_TYPE_ANY)
    {
        do {
            $line = $this->getLine();
            $this->lastLineType = $this->getLineType($line);

            switch ($this->lastLineType) {
                case self::LINE_TYPE_QUERY:
                    $this->lastLine = $this->parseQueryLine($line);
                    break;
                case self::LINE_TYPE_PARAM:
                    $this->lastLine = $this->parseParamLine($line);
                    break;
                case self::LINE_TYPE_META:
                    $this->lastDateTime = $this->parseDateLine($line);
                    $this->lastLogPosition = $this->parsePositionLine($line);
                    break;
                case self::LINE_TYPE_ERROR:
                    throw new \RuntimeException($this->parseErrorLine($line));
                default:
                    // Do nothing.
                    break;
            }
        } while ($type != self::LINE_TYPE_ANY
            && $this->lastLineType != $type
            && $this->getPipe()
            && !feof($this->getPipe())
        );

        return $this->lastLine;
    }

    /**
     * Detects line type.
     *
     * @param string $line
     *
     * @return int
     */
    protected function getLineType($line)
    {
        if (preg_match('/^###\s+@[0-9]+=.*$/', $line)) {
            return self::LINE_TYPE_PARAM;
        } elseif (preg_match('/^###/', $line)) {
            return self::LINE_TYPE_QUERY;
        } elseif (preg_match('/^#[0-9]/', $line)) {
            return self::LINE_TYPE_META;
        } elseif (preg_match('/Errcode|ERROR/', $line)) {
            return self::LINE_TYPE_ERROR;
        }

        return self::LINE_TYPE_UNKNOWN;
    }

    /**
     * Parses SQL query line.
     *
     * @param string $line
     *
     * @return string
     */
    protected function parseQueryLine($line)
    {
        return preg_replace('/^### /', '', $line);
    }

    /**
     * Parses SQL query meta line to extract date.
     *
     * @param string $line
     *
     * @return \DateTime
     */
    protected function parseDateLine($line)
    {
        $time = preg_replace('/^#([0-9]{2})([0-9]{2})([0-9]{2})\s+([0-9:]+?)\s.*/', '$1-$2-$3 $4', $line);

        return new \DateTime($time);
    }

    /**
     * Parses SQL query meta line to extract position.
     *
     * @param string $line
     *
     * @return int
     */
    protected function parsePositionLine($line)
    {
        $position = preg_replace('/^#.*end_log_pos\s([0-9]*)\s.*$/', '$1', $line);

        return (int)$position;
    }

    /**
     * Parses SQL parameter line.
     *
     * @param string $line
     *
     * @return string
     */
    protected function parseParamLine($line)
    {
        return preg_replace('/^###\s+/', '', $line);
    }

    /**
     * Parses error line.
     *
     * @param string $line
     *
     * @return string
     */
    protected function parseErrorLine($line)
    {
        return preg_replace('/^.*(Errcode:.*)\)$/', '$1', $line);
    }

    /**
     * Returns a pipe instance.
     *
     * @throws \RuntimeException
     * @return resource
     */
    protected function getPipe()
    {
        if ($this->pipe === null) {
            $this->getNewPipe();
        }

        return $this->pipe;
    }

    /**
     * Initializes a new pipe.
     *
     * @throws \RuntimeException
     */
    protected function getNewPipe()
    {
        $cmd = 'mysqlbinlog ' . escapeshellarg($this->logDir . '/' . $this->baseName) . '.[0-9]*';

        if ($this->from !== null) {
            if ($this->startType == self::START_TYPE_DATE) {
                $cmd .= ' --start-datetime=' . escapeshellarg($this->from->format('Y-m-d H:i:s'));
            } elseif ($this->startType == self::START_TYPE_POSITION) {
                $cmd .= ' --start-position=' . escapeshellarg($this->from);
            }
        }

        $cmd .= " --base64-output=DECODE-ROWS -v 2>&1 | grep -E '###|#[0-9]|Errcode|ERROR'";

        $this->pipe = popen($cmd, 'r');

        if (empty($this->pipe)) {
            throw new \RuntimeException('Error while executing mysqlbinlog');
        }
    }

    /**
     * Handles the beginning of a statement.
     *
     * @param string $line
     *
     * @throws \UnexpectedValueException
     * @return array|null
     */
    protected function handleStart($line)
    {
        if (preg_match('/^(INSERT INTO|UPDATE|DELETE FROM)\s+`?(.*?)`?\.`?(.*?)`?$/', $line, $part)) {
            return [
                'type' => $this->detectQueryType($part[1]),
                'table' => $part[3],
            ];
        }

        throw new \UnexpectedValueException("Expected a statement, got {$line}");
    }

    /**
     * Handle SQL statements.
     *
     * @param string $line
     * @param string $type
     *
     * @throws \UnexpectedValueException
     * @return array|null
     */
    protected function handleStatement($line, $type)
    {
        if (!preg_match("/^{$type}$/", $line)) {
            throw new \UnexpectedValueException("Expected a {$type} statement, got {$line}");
        }

        $params = [];
        $param = $this->handleParam();

        while ($param !== null) {
            $params = $params + $param;
            $param = $this->handleParam();
        }

        return $params;
    }

    /**
     * Handles a param, if the next line isn't a param, returns null.
     *
     * @return array|null
     */
    protected function handleParam()
    {
        if (preg_match('/^@([0-9]+)=(.*)$/', $this->getNextLine(self::LINE_TYPE_ANY), $part)) {
            $paramValue = trim($part[2], "'");

            return [$part[1] => $paramValue];
        }

        return null;
    }

    /**
     * Returns the job type based on statement.
     *
     * @param string $type
     *
     * @throws \UnexpectedValueException
     * @return string
     */
    protected function detectQueryType($type)
    {
        switch ($type) {
            case 'INSERT INTO':
                return SyncJob::TYPE_CREATE;
            case 'UPDATE':
                return SyncJob::TYPE_UPDATE;
            case 'DELETE FROM':
                return SyncJob::TYPE_DELETE;
            default:
                throw new \UnexpectedValueException("Unknown statement of type {$type}");
        }
    }
}
