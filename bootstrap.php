<?php
define('DS', DIRECTORY_SEPARATOR);
define('DEBUG', true);

/**
 * Check if script is running via CLI
 *
 * @return bool
 */
function is_cli()
{
    if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
        return true;
    }

    return false;
}

/**
 * Returns message only if DEBUG is true
 *
 * @param null $message
 * @param int $nl
 * @param bool $force
 */
function cli_message($message = null, $nl = 1, $force = false)
{
    if (is_cli()) {
        !DEBUG && !$force ?:
            print_r(
                $message . str_repeat(
                    "\r\n", is_null($nl) || $nl < 0 ? 1 : $nl
                )
            );
    }
}
