<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\Util\Uuid;

class SugarTestEmailAddressUtilities
{
    private static $createdAddresses = [];

    private function __construct()
    {
    }

    public static function createEmailAddress($address = null)
    {
        if (null === $address) {
            $address = 'address-' . Uuid::uuid1() . '@example.com';
        }

        $email_address = new EmailAddress();
        $email_address->email_address = $address;
        $email_address->email_address_caps = sugarStrToUpper($address);
        $email_address->save();

        self::$createdAddresses[] = $email_address;
        return $email_address;
    }

    /**
     * Add specified email address to the person
     *
     * @param SugarBean $person Any of the Person or Company beans.
     * @param string|EmailAddress $address
     * @param array $additional_values
     * @return boolean|EmailAddress
     * @throws InvalidArgumentException
     */
    public static function addAddressToPerson(SugarBean $person, $address, array $additional_values = [])
    {
        if (is_string($address)) {
            $address = self::createEmailAddress($address);
        }

        if (!$address instanceof EmailAddress) {
            throw new InvalidArgumentException(
                'Address must be a string or an instance of EmailAddress, '
                . gettype($address) . ' given'
            );
        }

        if (!$person->load_relationship('email_addresses')) {
            return false;
        }

        // create relation between user and email address
        $person->email_addresses->add([$address], $additional_values);
        $GLOBALS['db']->commit();
        return $address;
    }

    public static function removeAllCreatedAddresses()
    {
        $ids = self::getCreatedEmailAddressIds();
        if (safeCount($ids) > 0) {
            $GLOBALS['db']->query('DELETE FROM email_addresses WHERE id IN (\'' . implode("', '", $ids) . '\')');
        }
        self::$createdAddresses = [];
    }

    public static function getCreatedEmailAddressIds()
    {
        $ids = [];
        foreach (self::$createdAddresses as $address) {
            $ids[] = $address->id;
        }
        return $ids;
    }

    /**
     * Add one or more email address ID's that can be deleted later during tear down.
     *
     * @param array|string $ids
     */
    public static function setCreatedEmailAddress($ids)
    {
        $ids = is_array($ids) ? $ids : [$ids];

        foreach ($ids as $id) {
            $email = new EmailAddress();
            $email->id = $id;
            static::$createdAddresses[] = $email;
        }
    }

    /**
     * Add an email address that can be deleted later during tear down.
     *
     * @param string $address
     * @return string
     */
    public static function setCreatedEmailAddressByAddress($address)
    {
        $ea = BeanFactory::newBean('EmailAddresses');
        $id = $ea->getGuid($address);

        if (!empty($id)) {
            static::setCreatedEmailAddress($id);
        }

        return $id;
    }
}
