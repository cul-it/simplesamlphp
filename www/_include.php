<?php
/**
 * support for proper SERVER_NAME and SERVER_PORT on Pantheon
 * see https://pantheon.io/docs/server_name-and-server_port/#set-server_port-correctly
 * This affects the port for redirects after simplesamlphp
 * see RelayState in 
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
    $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];
    if (isset($_SERVER['HTTP_USER_AGENT_HTTPS']) && $_SERVER['HTTP_USER_AGENT_HTTPS'] === 'ON') {
      $_SERVER['SERVER_PORT'] = 443;
    }
    else {
      $_SERVER['SERVER_PORT'] = 80;
    }
    define("SIMPLESAMLPHP_PATH", $_ENV['HOME'] ."/files/private/cul-it-simplesamlphp");
    // initialize the autoloader
    require_once(SIMPLESAMLPHP_PATH.'/lib/_autoload.php');
  }
else {
    // initialize the autoloader
    require_once(dirname(dirname(__FILE__)).'/lib/_autoload.php');
}

// enable assertion handler for all pages
SimpleSAML_Error_Assertion::installHandler();

// show error page on unhandled exceptions
function SimpleSAML_exception_handler($exception)
{
    SimpleSAML\Module::callHooks('exception_handler', $exception);

    if ($exception instanceof SimpleSAML_Error_Error) {
        $exception->show();
    } elseif ($exception instanceof Exception) {
        $e = new SimpleSAML_Error_Error('UNHANDLEDEXCEPTION', $exception);
        $e->show();
    } else {
        if (class_exists('Error') && $exception instanceof Error) {
            $code = $exception->getCode();
            $errno = ($code > 0) ? $code : E_ERROR;
            $errstr = $exception->getMessage();
            $errfile = $exception->getFile();
            $errline = $exception->getLine();
            SimpleSAML_error_handler($errno, $errstr, $errfile, $errline);
        }
    }
}

set_exception_handler('SimpleSAML_exception_handler');

// log full backtrace on errors and warnings
function SimpleSAML_error_handler($errno, $errstr, $errfile = null, $errline = 0, $errcontext = null)
{
    if (!class_exists('SimpleSAML\Logger')) {
        /* We are probably logging a deprecation-warning during parsing. Unfortunately, the autoloader is disabled at
         * this point, so we should stop here.
         *
         * See PHP bug: https://bugs.php.net/bug.php?id=47987
         */
        return false;
    }

    if (SimpleSAML\Logger::isErrorMasked($errno)) {
        // masked error
        return false;
    }

    static $limit = 5;
    $limit -= 1;
    if ($limit < 0) {
        // we have reached the limit in the number of backtraces we will log
        return false;
    }

    // show an error with a full backtrace
    $e = new SimpleSAML_Error_Exception('Error '.$errno.' - '.$errstr);
    $e->logError();

    // resume normal error processing
    return false;
}

set_error_handler('SimpleSAML_error_handler');

try {
    SimpleSAML_Configuration::getInstance();
} catch (Exception $e) {
    throw new \SimpleSAML\Error\CriticalConfigurationError(
        $e->getMessage()
    );
}

// set the timezone
SimpleSAML\Utils\Time::initTimezone();
