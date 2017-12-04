<?php
/**
 * @package   Divante\LocaleDateTime
 * @author    Marek Mularczyk <mmularczyk@divante.pl>
 * @copyright 2017 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\LocaleDateTime\Framework\Stdlib\DateTime\Filter;

use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date/Time filter. Converts datetime from localized to internal format.
 *
 * @api
 */
class DateTime extends Date
{
    /**
     * Mapper which returns correct date format for IntlDateFormatter
     */
    const DATE_FORMAT_MAPPER = [
        'dd/MM/y' => 'd/m/Y',
        'M/d/yy'  => 'm/d/Y',
    ];

    /**
     * @var Session
     */
    protected $authUser;

    /**
     * DateTime constructor.
     *
     * @param TimezoneInterface $localeDate
     * @param Session           $authUser
     */
    public function __construct(TimezoneInterface $localeDate, Session $authUser)
    {
        $this->authUser = $authUser;

        parent::__construct($localeDate);
    }

    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \Exception
     */
    public function filter($value)
    {
        try {
            $user       = $this->authUser->getUser();
            $userLocale = $user->getInterfaceLocale();
            $date       = date_create_from_format($this->getDateFormat($userLocale), $value);

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid input datetime format of value '$value'", $e->getCode(), $e);
        }
    }

    /**
     * @param string $locale
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getDateFormat($locale)
    {
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);

        if (null === $formatter) {
            throw new \Exception(intl_get_error_message());
        }

        if (!isset(self::DATE_FORMAT_MAPPER[$formatter->getPattern()])) {
            throw new \Exception(sprintf(
                    'Could not find correct date format fot selected pattern %s',
                    $formatter->getPattern())
            );
        }

        return self::DATE_FORMAT_MAPPER[$formatter->getPattern()];
    }
}
