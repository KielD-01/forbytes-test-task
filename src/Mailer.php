<?php

namespace Forbytes\Test;

use Error;

/**
 * Class Mailer
 * @package Fobytes\Test
 * @property-read string templatesPath
 * @property string template
 * @property string to
 * @property string from
 * @property string subject
 * @property array headers
 */
class Mailer
{

    private $templatesPath = __DIR__ . DS . '..' . DS . 'templates' . DS . 'email' . DS;

    private $template = null;

    private $headers = [
        'from' => null,
        'reply' => null,
        'content' => null,
        'mime' => 'MIME-Version: 1.0'
    ];

    /**
     * Mailer constructor.
     * @param null $path
     */
    public function __construct($path = null)
    {
        if ($path) {
            $this->loadTemplate($path);
        }
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->to = null;
        $this->from = null;
        $this->subject = null;
        $this->headers = [
            'from' => null,
            'reply' => null,
            'content' => 'Content-Type: text/html; charset=ISO-8859-1',
            'mime' => 'MIME-Version: 1.0'
        ];
        $this->template = null;
    }

    /**
     * @param null $to
     * @return $this
     */
    public function setTo($to = null)
    {
        if (!$to) {
            throw new Error('Recipient can\'t be null');
        }

        $this->to = $to;
        return $this;
    }

    /**
     * @param null $from
     * @return $this
     */
    public function setFrom($from = null)
    {
        if (!$from) {
            throw new Error('Sender can\'t be null');
        }

        $this->from = $from;
        $this->headers['from'] = "From: {$from}";
        $this->headers['reply'] = "Reply-To: {$from}";

        return $this;
    }

    /**
     * @param null $subject
     * @return Mailer
     */
    public function setSubject($subject = null)
    {
        if (!$subject) {
            throw new Error('Subject can\'t be null');
        }

        $this->subject = $subject;
        return $this;
    }

    /**
     * @param null $path
     * @return $this
     */
    public function loadTemplate($path = null)
    {
        if (!$path) {
            throw new Error("Path hasn't been defined in a proper way : {$path}");
        }

        $this->makePath($path);

        if (!is_file($path)) {
            throw new Error("Template `{$path}` does not exists");
        }

        $this->template = file_get_contents($path);
        return $this;
    }

    /**
     * Makes path to a template
     *
     * @param $path
     * @return bool
     */
    private function makePath(&$path)
    {
        if (is_string($path)) {
            $path = $this->templatesPath . $path . '.html';
            return true;
        }

        if (is_array($path)) {
            $path = $this->templatesPath . implode(DS, $path) . DS;
            return true;
        }

        return false;
    }

    /**
     * @param array $vars
     */
    private function replacePlaceholders($vars = [])
    {
        $this->template = preg_replace(
            $this->transformIntoRegex(array_keys($vars)),
            array_values($vars),
            $this->template
        );
    }

    /**
     * Transforming values into regex
     *
     * @param array $values
     * @return array
     */
    private function transformIntoRegex($values = [])
    {
        foreach ($values as $index => $value) {
            $values[$index] = "/\{{$value}\}/";
        }

        return $values;
    }

    /**
     * @return bool
     */
    private function checkEmptyPlaceholders()
    {
        if (preg_match_all('/\{(\w+)\}/', $this->template, $matches)) {
            $missedFields = implode(', ', $matches[1]);

            if (is_cli()) {
                $missedFields = "\e[0;37m" . $missedFields . "\e[0m";
            }

            throw new Error("There's not replaced values : " . $missedFields);
        }

        return true;
    }

    /**
     * @return string
     */
    private function makeHeaders()
    {
        return implode("\r\n", array_filter($this->headers));
    }

    /**
     * @param array $vars
     */
    public function send($vars = [])
    {

        if (!$this->template) {
            throw new Error('No template has been loaded. Use `Mailer::load_template($path = null)` method to load existing E-Mail template');
        }

        $this->replacePlaceholders(array_filter($vars));
        $this->checkEmptyPlaceholders();

        if (!$this->to) {
            throw new Error('Recipient is not valid');
        }

        mail(
            $this->to,
            $this->subject,
            $this->template,
            $this->makeHeaders()
        );

        $this->reset();
    }
}
