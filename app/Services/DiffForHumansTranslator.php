<?php

namespace App\Services;

use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\TranslatorInterface;

class DiffForHumansTranslator implements TranslatorInterface
{
    protected $messages = [
        'cs' => [
            'year'          => '1 rok|:count roky|:count let',
            'month'         => '1 měsíc|:count měsíce|:count měsíců',
            'week'          => '1 týden|:count týdny|:count týdnů',
            'day'           => '1 den|:count dny|:count dní',
            'hour'          => '1 hodinu|:count hodiny|:count hodin',
            'minute'        => '1 minutu|:count minuty|:count minut',
            'second'        => '1 sekundu|:count sekundy|:count sekund',
            'year_ago'      => '1 rokem|:count roky|:count roky',
            'month_ago'     => '1 měsícem|:count měsíci|:count měsíci',
            'week_ago'      => '1 týdnem|:count týdny|:count týdnů',
            'day_ago'       => '1 dnem|:count dny|:count dny',
            'hour_ago'      => '1 hodinou|:count hodinami|:count hodinami',
            'minute_ago'    => '1 minutou|:count minutami|:count minutami',
            'second_ago'    => '1 sekundou|:count sekundami|:count sekundami',
            'ago'           => 'před :time',
            'from_now'      => 'za :time',
            'after'         => ':time později',
            'before'        => ':time předtím'
            ],
        'en' => [
            'year'          => '1 year|:count years',
            'month'         => '1 month|:count months',
            'week'          => '1 week|:count weeks',
            'day'           => '1 day|:count days',
            'hour'          => '1 hour|:count hours',
            'minute'        => '1 minute|:count minutes',
            'second'        => '1 second|:count seconds',
            'year_ago'      => '1 year|:count years',
            'month_ago'     => '1 month|:count months',
            'week_ago'      => '1 week|:count weeks',
            'day_ago'       => '1 day|:count days',
            'hour_ago'      => '1 hour|:count hours',
            'minute_ago'    => '1 minute|:count minutes',
            'second_ago'    => '1 second|:count seconds',
            'ago'           => ':time ago',
            'from_now'      => ':time from now',
            'after'         => ':time after',
            'before'        => ':time before',
        ],
        'ru' => [
            'year'          => ':count год|:count года|:count лет',
            'month'         => ':count месяц|:count месяца|:count месяцев',
            'week'          => ':count неделю|:count недели|:count недель',
            'day'           => ':count день|:count дня|:count дней',
            'hour'          => ':count час|:count часа|:count часов',
            'minute'        => ':count минуту|:count минуты|:count минут',
            'second'        => ':count секунду|:count секунды|:count секунд',
            'year_ago'      => ':count год|:count года|:count лет',
            'month_ago'     => ':count месяц|:count месяца|:count месяцев',
            'week_ago'      => ':count неделю|:count недели|:count недель',
            'day_ago'       => ':count день|:count дня|:count дней',
            'hour_ago'      => ':count час|:count часа|:count часов',
            'minute_ago'    => ':count минуту|:count минуты|:count минут',
            'second_ago'    => ':count секунду|:count секунды|:count секунд',
            'ago'           => ':time назад',
            'from_now'      => 'через :time',
            'after'         => ':time после',
            'before'        => ':time до'
        ]
    ];

    /**
     * @var string
     */
    protected $locale;


    /**
     * Constructor.
     *
     * @param string               $locale   The locale
     * @param MessageSelector|null $selector The message selector for pluralization
     * @param string|null          $cacheDir The directory to use for the cache
     * @param bool                 $debug    Use cache in debug mode ?
     *
     * @throws \InvalidArgumentException If a locale contains invalid characters
     */
    public function __construct($locale, MessageSelector $selector = null, $cacheDir = null, $debug = false)
    {
        $this->setLocale($locale);
    }

    /**
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @return string The translated string
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $choice = $this->messages[$locale ?: $this->locale][$id];
        foreach($parameters as $key => $value) {
            $choice = str_replace($key, $value, $choice);
        }
        return $choice;
    }

    /**
     * Translates the given choice message by choosing a translation according to a number.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param int         $number     The number to use to find the indice of the message
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @return string The translated string
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $choices = @$this->messages[$locale ?: $this->locale][$id];
        if(is_null($choices)) {
            return $id;
        }
        $choices = explode('|', $choices);
        if ($number == 1) {
            $choice = $choices[0];
        } else if ($number && $number <= 4) {
            $choice = $choices[1];
        } else {
            $choice = $choices[2];
        }

        $choice = str_replace(':count', $number, $choice);
        foreach($parameters as $key => $value) {
            $choice = str_replace($key, $value, $choice);
        }

        return $choice;
    }

    /**
     * Sets the current locale.
     *
     * @param string $locale The locale
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function setLocale($locale)
    {
        $this->assertValidLocale($locale);
        $this->locale = $locale;
    }

    /**
     * Returns the current locale.
     *
     * @return string The locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale($locale)
    {
        if (1 !== preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
            throw new \InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }

    /**
     * Adds a Resource.
     *
     * @param string $format   The name of the loader (@see addLoader())
     * @param mixed  $resource The resource name
     * @param string $locale   The locale
     * @param string $domain   The domain
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function addResource($format, $resource, $locale, $domain = null)
    {
        //this is important for Carbo
        return null;
    }

}