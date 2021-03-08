<?php
// TODO add option to use colorful CLI output using the unix code like \e[0;31m
class Cemes_ErrorHandler extends Cemes_Pattern_Singleton
{
    /**
     * The instance of this class.
     *
     * @var Cemes_ErrorHandler
     */
    protected static $instance;

    /**
     * Array of fatal errors.
     *
     * @var array
     */
    protected static $fatalErrors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    /**
     * Array of errors.
     *
     * @var array
     */
    protected static $errors = [];

    /**
     * If the error handler has been initialized.
     *
     * @var bool
     */
    protected $_isEnabled = false;

    /**
     * If the error that should be processed is a fatal error.
     *
     * @var bool
     */
    protected $_fatalError = false;

    /**
     * Registers error handler, exception handler and fatal error handler.
     */
    public static function registerErrorHandler()
    {
        $instance = static::getInstance();

        set_error_handler([$instance, 'handleError']);
        if (version_compare(PHP_VERSION_ID, 70000, '<')) {
            set_exception_handler([$instance, 'handleExceptions']);
        } else {
            set_exception_handler([$instance, 'handleThrowables']);
        }
        register_shutdown_function([$instance, 'handleFatalError']);

        $instance->_isEnabled = true;
    }

    /**
     * Handles PHP errors.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile | optional
     * @param int    $errline | optional
     * @param array  $context | optional
     *
     * @return bool
     */
    public function handleError($errno, $errstr, $errfile = null, $errline = null, $context = null)
    {
        if (($errno & error_reporting()) !== $errno) {
            return false;
        }

        $errorHandler = Cemes_ErrorHandler::getInstance();
        // if errors should be logged in a file do so
        if ($GLOBALS['CEMES_CONFIG']['ERRORS']['logErrors']) {
            $errorHandler->logError($errno, $errstr, $errfile, $errline);
        }

        // process error and build array with more information from it
        $error = [];

        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $error['type'] = 'Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $error['type'] = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $error['type'] = 'Notice';
                break;
            default:
                $error['type'] = $this->codeToString($errno);
        }
        if ($this->_fatalError) {
            $error['type'] = 'Fatal Error';
        }
        $trace = debug_backtrace();
        // go back the stack trace until we find a file that is not the very base of the framework
        while (preg_match('/Autoloader|ErrorHandler|cemes\.php/', $errfile)) {
            $caller = next($trace);

            // if trace entry does not have a file continue to the next one
            while ($caller && !array_key_exists('file', $caller)) {
                $caller = next($trace);
            }

            if (!$caller) {
                break;
            }

            $errfile = $caller['file'];
            $errline = $caller['line'];
        }
        $error['errno'] = $errorHandler->codeToString($errno);
        $error['errstr'] = str_replace(' and defined', '', $errstr);
        $error['errfile'] = str_replace([ROOT_BASEDIR, CEMES_BASEDIR], '', $errfile);
        $error['errline'] = $errline;
        if ($GLOBALS['CEMES_CONFIG']['ERRORS']['debug']) {
            $error['context'] = $context;
        }

        static::$errors[] = $error;

        // ignore further error handling from PHP
        return true;
    }

    /**
     * Handles not caught exception. Execution will be stopped after this method was called.
     *
     * @param Exception $exception
     */
    public function handleExceptions(Exception $exception)
    {
        $this->handleError(
            $exception->getCode(),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        $this->lastError();
    }

    /**
     * Handles not caught throwable. Execution will be stopped after this method was called.
     *
     * @param \Throwable $throwable
     */
    public function handleThrowables(Throwable $throwable)
    {
        $this->handleError(
            $throwable->getCode(),
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine()
        );
        $this->lastError();
    }

    /**
     * If the last error was a fatal error the error will be handled.
     */
    public function handleFatalError()
    {
        $lastError = error_get_last();

        if ($lastError && in_array($lastError['type'], static::$fatalErrors, true)) {
            $this->_fatalError = true;
            $this->handleError(
                $lastError['type'],
                $lastError['message'],
                $lastError['file'],
                $lastError['line']
            );
            $this->lastError();
        }
    }

    /**
     * Method to write error in log file with date/time and IP.
     *
     * @param string|int $errno
     * @param string     $errstr  | optional
     * @param string     $errfile | optional
     * @param int        $errline | optional
     *
     * @throws \Exception
     */
    public function logError($errno, $errstr = null, $errfile = null, $errline = null)
    {
        if (!$this->_isEnabled) {
            return;
        }

        if ($handle = fopen($GLOBALS['CEMES_CONFIG']['ERRORS']['logFile'], 'ab')) {
            $time = new DateTime();
            $string = $time->format('Y-m-d - H:i:s') . ' | ' . $_SERVER['REMOTE_ADDR'] . ' | ';
            switch ($errno) {
                case E_ERROR:
                case E_USER_ERROR:
                    $string .= 'ERROR ';
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $string .= 'WARNING ';
                    break;
                case E_NOTICE:
                case E_USER_NOTICE:
                    $string .= 'NOTICE ';
                    break;
                default:
                    $string .= $errno . ' ';
            }
            $string .= 'File: ' . str_replace(CEMES_BASEDIR, '', $errfile);
            $string .= ' | Line: ' . $errline;
            $string .= ' | ' . $errstr . '\r\n';
            fwrite($handle, $string);
            fclose($handle);
        } else {
            throw new Exception('Logfile-Error');
        }
    }

    /**
     * Displays the last error.
     */
    public function lastError()
    {
        if (!$this->_isEnabled) {
            echo 'Der Cemes eigene Error Handler ist nicht aktiv, bitte aktivieren Sie ihn mit Cemes::handelErrors().';

            return;
        }

        $error = array_pop(static::$errors);

        if (Cemes::isCli()) {
            $this->_displayCliError($error);
        } else {
            // make sure styling is present in html context
            Cemes::getInstance()->insertCSS();

            $this->_displayHtmlError($error);
        }
    }

    /**
     * Displays the given amount of errors. If $number is null all errors will be displayed.
     *
     * @param int $number
     */
    public function displayErrors($number = null)
    {
        if (!$this->_isEnabled) {
            echo 'Der Cemes eigene Error Handler ist nicht aktiv, bitte aktivieren Sie ihn mit Cemes::handelErrors().';

            return;
        }

        // make sure styling is present in html context
        Cemes::getInstance()->insertCSS();

        if (null !== $number) {
            $number = ($number < count(static::$errors)) ? $number : count(static::$errors);
        } else {
            $number = count(static::$errors);
        }

        for ($i = 0; $i < $number; $i++) {
            $error = static::$errors[$i];
            unset(static::$errors[$i]);

            if (Cemes::isCli()) {
                $this->_displayCliError($error);
            } else {
                $this->_displayHtmlError($error);
            }
        }
    }

    /**
     * Displays the given error in a cli environment.
     *
     * @param array $error
     */
    protected function _displayCliError($error)
    {
        $displaytext = PHP_EOL;
        if (!array_key_exists('type', $error)) {
            $displaytext .= $error['errstr'];
            $displaytext .= 'File: ' . $error['errfile'] . ' in line ' . $error['errline'] . PHP_EOL;
            $displaytext .= 'PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . PHP_EOL;
        } else {
            $displaytext .= $error['type'] . ' ';
            if (array_key_exists('caller', $error) && $error['caller']) {
                $displaytext .= 'Ausl&ouml;ser: ' . $error['caller'] . ' ';
            }
            $displaytext .= '[' . $error['errno'] . ']:' . PHP_EOL;
            $displaytext .= $error['errstr'] . PHP_EOL;
            $displaytext .= 'File: ' . $error['errfile'] . ' in line ' . $error['errline'] . PHP_EOL;
            $displaytext .= 'PHP ' . PHP_VERSION . ' (' . PHP_OS . ')' . PHP_EOL;
            if (array_key_exists('context', $error) && $error['context']) {
                $displaytext .= 'Context:' . PHP_EOL;
                $displaytext .= print_r($error['context'], true);
            }
        }

        echo $displaytext;
    }

    /**
     * Displays the given error in a non cli environment (HTML).
     *
     * @param array $error
     */
    protected function _displayHtmlError($error)
    {
        $displaytext = '<div class="CemesMsg_';
        switch ($error['errno']) {
            case 'E_ERROR':
            case 'E_USER_ERROR':
                $displaytext .= 'error';
                break;
            case 'E_WARNING':
            case 'E_USER_WARNING':
                $displaytext .= 'warning';
                break;
            case 'E_NOTICE':
            case 'E_USER_NOTICE':
                $displaytext .= 'notice';
                break;
            case 'SUCCESS':
                $displaytext .= 'success';
                break;
            default:
                $displaytext .= 'default';
        }
        $displaytext .= '">' . "\r\n";

        if (!array_key_exists('type', $error)) {
            $displaytext .= $error['errstr'];
        } else {
            $displaytext .= '<b>' . $error['type'] . "</b><br>\r\n";
            if (array_key_exists('caller', $error) && $error['caller']) {
                $displaytext .= 'Ausl&ouml;ser: ' . $error['caller'] . "<br>\r\n";
            }
            $displaytext .= '[' . $error['errno'] . '] :<br>' . $error['errstr'] . "<br>\r\n";
            $displaytext .= 'File: ' . $error['errfile'] . ' in line ' . $error['errline'] . "<br>\r\n";
            $displaytext .= 'PHP ' . PHP_VERSION . ' (' . PHP_OS . ")<br>\r\n";
            if (array_key_exists('context', $error) && $error['context']) {
                $displaytext .= "Context:<br>\r\n";
                $displaytext .= '<pre>' . print_r($error['context'], true) . '</pre>';
            }
        }

        $displaytext .= "</div>\r\n";
        echo $displaytext;
    }

    /**
     * Returns the given error code as a human readable string.
     *
     * @param int $code
     *
     * @return string
     */
    protected function codeToString($code)
    {
        switch ($code) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }

        return 'Unknown PHP error';
    }
}
