<?php
require_once 'vendor/autoload.php';

use Forbytes\Test\DataLayer;
use Forbytes\Test\Mailer;
use Forbytes\Test\Models\Customer;

$statistics = [
    'sent' => 0,
    'failed' => 0
];

function processListCustomers($callback)
{
    array_map($callback, DataLayer::ListCustomers());
}

function sendWelcomeEmails(&$statistics = [])
{
    cli_message("\e[0;34m======= Sending Emails To a New Customers has been started ========\e[0m");

    $mailer = new Mailer();

    processListCustomers(function (Customer $customer) use ($mailer, &$statistics) {
        if ($customer->isNewCustomer()) {
            try {
                $mailer
                    ->loadTemplate('hello-email')
                    ->setSubject('Welcome as a new customer')
                    ->setFrom("info@forbytes.com")
                    ->setTo($customer->email)
                    ->send(['email' => $customer->email]);

                cli_message(
                    "\e[0;32mSuccess\e[0m : Email has been successfully sent to a recipient ({$customer->email})"
                );
                $statistics['sent']++;
            } catch (Error $error) {
                cli_message(
                    "\033[0;31mError\033[0m : Failed to send an E-Mail to \e[1;33m{$customer->email}\e[0m, because {$error->getMessage()}"
                );
                $statistics['failed']++;
            }
        }
    });

    cli_message("\e[0;34m======= Sending Emails To a New Customers has been finished ========\e[0m", 2);
}

function sendEmailsToInactiveCustomers(&$statistics, $voucher = null)
{
    cli_message("\e[0;34m======= Sending Emails To Inactive Customers has been started ========\e[0m");

    if (is_null($voucher) || !$voucher || preg_match('/([0-9a-zA-Z]+)/', $voucher)) {
        cli_message("\e[0;31mError\e[0m : No voucher code has been provided");
    }

    if (!DEBUG && !(date('D', time()) === 'Mon')) {
        cli_message("\e[0;31mWarning\e[0m : Debug mode is turned off. So, the mails will be sending only on Monday");
        cli_message("\e[0;34m======= Sending Emails To Inactive Customers has been finished ========\e[0m", 2);
        return;
    }

    $mailer = new Mailer();

    processListCustomers(function (Customer $customer) use ($mailer, $voucher, &$statistics) {
        if ($customer->hasNoOrders()) {
            try {
                $mailer
                    ->loadTemplate('come-back-email')
                    ->setSubject('We miss you as a customer')
                    ->setFrom("info@forbytes.com")
                    ->setTo($customer->email)
                    ->send([
                        'email' => $customer->email,
                        'voucher' => $voucher
                    ]);

                cli_message(
                    "\e[0;32mSuccess\e[0m : Email has been successfully sent to a recipient ({$customer->email}). Voucher code \e[0;32m{$voucher}\e[0m"
                );

                $statistics['sent']++;
            } catch (Error $error) {
                cli_message(
                    "\033[0;31mError\033[0m : Failed to send an E-Mail to \e[1;33m{$customer->email}\e[0m, because {$error->getMessage()}"
                );

                $statistics['failed']++;
            }

            return true;
        }

        cli_message(
            "\e[0;31mWarning\e[0m : Customer \e[1;33m{$customer->email}\e[0m is not going to be proceeded with come back emails, because do have an orders"
        );
    });

    cli_message("\e[0;34m======= Sending Emails To Inactive Customers has been finished ========\e[0m", 2);
}

function sendAnotherTypeOfEmailToCustomers(&$statistics)
{
    cli_message("\e[0;34m======= Sending NY Emails has been started ========\e[0m");

    if (date('m.d') !== '31.12') {
        cli_message("\e[0;31mWarning\e[0m : It's not a NY yet :)");
        cli_message("\e[0;34m======= Sending NY Emails has been finished ========\e[0m", 2);
        return;
    }

    $mailer = new Mailer('ny-email');

    processListCustomers(function (Customer $customer) use ($mailer, &$statistics) {
        try {
            $mailer
                ->setSubject('Happy New Year!')
                ->setFrom("info@forbytes.com")
                ->setTo($customer->email)
                ->send(['email' => $customer->email]);

            cli_message(
                "\e[0;32mSuccess\e[0m : Email has been successfully sent to a recipient ({$customer->email})"
            );
            $statistics['sent']++;
        } catch (Error $error) {
            cli_message(
                "\033[0;31mError\033[0m : Failed to send an E-Mail to \e[1;33m{$customer->email}\e[0m, because {$error->getMessage()}"
            );
            $statistics['failed']++;
        }
    });

    cli_message("\e[0;34m======= Sending NY Emails has been finished ========\e[0m");
}

sendWelcomeEmails($statistics);
sendEmailsToInactiveCustomers($statistics, 'coupon-code-hello-world');
sendAnotherTypeOfEmailToCustomers($statistics);

$elapsed['finish'] = time();

cli_message("\e[0;34m======= Statistics =======\e[0m", null, true);
cli_message("\e[0;32mSent\e[0m : {$statistics['sent']}", null, true);
cli_message("\e[0;31mFailed\e[0m : {$statistics['failed']}", null, true);
