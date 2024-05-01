<?php
/**
 * AccountPasswordRules
 *
 * PHP version 7.4
 *
 * @category Class
 * @package  DocuSign\eSign
 * @author   Swagger Codegen team <apihelp@docusign.com>
 * @license  The DocuSign PHP Client SDK is licensed under the MIT License.
 * @link     https://github.com/swagger-api/swagger-codegen
 */

/**
 * DocuSign REST API
 *
 * The DocuSign REST API provides you with a powerful, convenient, and simple Web services API for interacting with DocuSign.
 *
 * OpenAPI spec version: v2.1
 * Contact: devcenter@docusign.com
 * Generated by: https://github.com/swagger-api/swagger-codegen.git
 * Swagger Codegen version: 2.4.21-SNAPSHOT
 */

/**
 * NOTE: This class is auto generated by the swagger code generator program.
 * https://github.com/swagger-api/swagger-codegen
 * Do not edit the class manually.
 */

namespace DocuSign\eSign\Model;

use \ArrayAccess;
use DocuSign\eSign\ObjectSerializer;

/**
 * AccountPasswordRules Class Doc Comment
 *
 * @category    Class
 * @package     DocuSign\eSign
 * @author      Swagger Codegen team <apihelp@docusign.com>
 * @license     The DocuSign PHP Client SDK is licensed under the MIT License.
 * @link        https://github.com/swagger-api/swagger-codegen
 */
class AccountPasswordRules implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'accountPasswordRules';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'expire_password' => '?string',
        'expire_password_days' => '?string',
        'expire_password_days_metadata' => '\DocuSign\eSign\Model\AccountPasswordExpirePasswordDays',
        'lockout_duration_minutes' => '?string',
        'lockout_duration_minutes_metadata' => '\DocuSign\eSign\Model\AccountPasswordLockoutDurationMinutes',
        'lockout_duration_type' => '?string',
        'lockout_duration_type_metadata' => '\DocuSign\eSign\Model\AccountPasswordLockoutDurationType',
        'minimum_password_age_days' => '?string',
        'minimum_password_age_days_metadata' => '\DocuSign\eSign\Model\AccountPasswordMinimumPasswordAgeDays',
        'minimum_password_length' => '?string',
        'minimum_password_length_metadata' => '\DocuSign\eSign\Model\AccountMinimumPasswordLength',
        'password_include_digit' => '?string',
        'password_include_digit_or_special_character' => '?string',
        'password_include_lower_case' => '?string',
        'password_include_special_character' => '?string',
        'password_include_upper_case' => '?string',
        'password_strength_type' => '?string',
        'password_strength_type_metadata' => '\DocuSign\eSign\Model\AccountPasswordStrengthType',
        'questions_required' => '?string',
        'questions_required_metadata' => '\DocuSign\eSign\Model\AccountPasswordQuestionsRequired'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'expire_password' => null,
        'expire_password_days' => null,
        'expire_password_days_metadata' => null,
        'lockout_duration_minutes' => null,
        'lockout_duration_minutes_metadata' => null,
        'lockout_duration_type' => null,
        'lockout_duration_type_metadata' => null,
        'minimum_password_age_days' => null,
        'minimum_password_age_days_metadata' => null,
        'minimum_password_length' => null,
        'minimum_password_length_metadata' => null,
        'password_include_digit' => null,
        'password_include_digit_or_special_character' => null,
        'password_include_lower_case' => null,
        'password_include_special_character' => null,
        'password_include_upper_case' => null,
        'password_strength_type' => null,
        'password_strength_type_metadata' => null,
        'questions_required' => null,
        'questions_required_metadata' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'expire_password' => 'expirePassword',
        'expire_password_days' => 'expirePasswordDays',
        'expire_password_days_metadata' => 'expirePasswordDaysMetadata',
        'lockout_duration_minutes' => 'lockoutDurationMinutes',
        'lockout_duration_minutes_metadata' => 'lockoutDurationMinutesMetadata',
        'lockout_duration_type' => 'lockoutDurationType',
        'lockout_duration_type_metadata' => 'lockoutDurationTypeMetadata',
        'minimum_password_age_days' => 'minimumPasswordAgeDays',
        'minimum_password_age_days_metadata' => 'minimumPasswordAgeDaysMetadata',
        'minimum_password_length' => 'minimumPasswordLength',
        'minimum_password_length_metadata' => 'minimumPasswordLengthMetadata',
        'password_include_digit' => 'passwordIncludeDigit',
        'password_include_digit_or_special_character' => 'passwordIncludeDigitOrSpecialCharacter',
        'password_include_lower_case' => 'passwordIncludeLowerCase',
        'password_include_special_character' => 'passwordIncludeSpecialCharacter',
        'password_include_upper_case' => 'passwordIncludeUpperCase',
        'password_strength_type' => 'passwordStrengthType',
        'password_strength_type_metadata' => 'passwordStrengthTypeMetadata',
        'questions_required' => 'questionsRequired',
        'questions_required_metadata' => 'questionsRequiredMetadata'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'expire_password' => 'setExpirePassword',
        'expire_password_days' => 'setExpirePasswordDays',
        'expire_password_days_metadata' => 'setExpirePasswordDaysMetadata',
        'lockout_duration_minutes' => 'setLockoutDurationMinutes',
        'lockout_duration_minutes_metadata' => 'setLockoutDurationMinutesMetadata',
        'lockout_duration_type' => 'setLockoutDurationType',
        'lockout_duration_type_metadata' => 'setLockoutDurationTypeMetadata',
        'minimum_password_age_days' => 'setMinimumPasswordAgeDays',
        'minimum_password_age_days_metadata' => 'setMinimumPasswordAgeDaysMetadata',
        'minimum_password_length' => 'setMinimumPasswordLength',
        'minimum_password_length_metadata' => 'setMinimumPasswordLengthMetadata',
        'password_include_digit' => 'setPasswordIncludeDigit',
        'password_include_digit_or_special_character' => 'setPasswordIncludeDigitOrSpecialCharacter',
        'password_include_lower_case' => 'setPasswordIncludeLowerCase',
        'password_include_special_character' => 'setPasswordIncludeSpecialCharacter',
        'password_include_upper_case' => 'setPasswordIncludeUpperCase',
        'password_strength_type' => 'setPasswordStrengthType',
        'password_strength_type_metadata' => 'setPasswordStrengthTypeMetadata',
        'questions_required' => 'setQuestionsRequired',
        'questions_required_metadata' => 'setQuestionsRequiredMetadata'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'expire_password' => 'getExpirePassword',
        'expire_password_days' => 'getExpirePasswordDays',
        'expire_password_days_metadata' => 'getExpirePasswordDaysMetadata',
        'lockout_duration_minutes' => 'getLockoutDurationMinutes',
        'lockout_duration_minutes_metadata' => 'getLockoutDurationMinutesMetadata',
        'lockout_duration_type' => 'getLockoutDurationType',
        'lockout_duration_type_metadata' => 'getLockoutDurationTypeMetadata',
        'minimum_password_age_days' => 'getMinimumPasswordAgeDays',
        'minimum_password_age_days_metadata' => 'getMinimumPasswordAgeDaysMetadata',
        'minimum_password_length' => 'getMinimumPasswordLength',
        'minimum_password_length_metadata' => 'getMinimumPasswordLengthMetadata',
        'password_include_digit' => 'getPasswordIncludeDigit',
        'password_include_digit_or_special_character' => 'getPasswordIncludeDigitOrSpecialCharacter',
        'password_include_lower_case' => 'getPasswordIncludeLowerCase',
        'password_include_special_character' => 'getPasswordIncludeSpecialCharacter',
        'password_include_upper_case' => 'getPasswordIncludeUpperCase',
        'password_strength_type' => 'getPasswordStrengthType',
        'password_strength_type_metadata' => 'getPasswordStrengthTypeMetadata',
        'questions_required' => 'getQuestionsRequired',
        'questions_required_metadata' => 'getQuestionsRequiredMetadata'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    

    

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['expire_password'] = isset($data['expire_password']) ? $data['expire_password'] : null;
        $this->container['expire_password_days'] = isset($data['expire_password_days']) ? $data['expire_password_days'] : null;
        $this->container['expire_password_days_metadata'] = isset($data['expire_password_days_metadata']) ? $data['expire_password_days_metadata'] : null;
        $this->container['lockout_duration_minutes'] = isset($data['lockout_duration_minutes']) ? $data['lockout_duration_minutes'] : null;
        $this->container['lockout_duration_minutes_metadata'] = isset($data['lockout_duration_minutes_metadata']) ? $data['lockout_duration_minutes_metadata'] : null;
        $this->container['lockout_duration_type'] = isset($data['lockout_duration_type']) ? $data['lockout_duration_type'] : null;
        $this->container['lockout_duration_type_metadata'] = isset($data['lockout_duration_type_metadata']) ? $data['lockout_duration_type_metadata'] : null;
        $this->container['minimum_password_age_days'] = isset($data['minimum_password_age_days']) ? $data['minimum_password_age_days'] : null;
        $this->container['minimum_password_age_days_metadata'] = isset($data['minimum_password_age_days_metadata']) ? $data['minimum_password_age_days_metadata'] : null;
        $this->container['minimum_password_length'] = isset($data['minimum_password_length']) ? $data['minimum_password_length'] : null;
        $this->container['minimum_password_length_metadata'] = isset($data['minimum_password_length_metadata']) ? $data['minimum_password_length_metadata'] : null;
        $this->container['password_include_digit'] = isset($data['password_include_digit']) ? $data['password_include_digit'] : null;
        $this->container['password_include_digit_or_special_character'] = isset($data['password_include_digit_or_special_character']) ? $data['password_include_digit_or_special_character'] : null;
        $this->container['password_include_lower_case'] = isset($data['password_include_lower_case']) ? $data['password_include_lower_case'] : null;
        $this->container['password_include_special_character'] = isset($data['password_include_special_character']) ? $data['password_include_special_character'] : null;
        $this->container['password_include_upper_case'] = isset($data['password_include_upper_case']) ? $data['password_include_upper_case'] : null;
        $this->container['password_strength_type'] = isset($data['password_strength_type']) ? $data['password_strength_type'] : null;
        $this->container['password_strength_type_metadata'] = isset($data['password_strength_type_metadata']) ? $data['password_strength_type_metadata'] : null;
        $this->container['questions_required'] = isset($data['questions_required']) ? $data['questions_required'] : null;
        $this->container['questions_required_metadata'] = isset($data['questions_required_metadata']) ? $data['questions_required_metadata'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];

        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }


    /**
     * Gets expire_password
     *
     * @return ?string
     */
    public function getExpirePassword()
    {
        return $this->container['expire_password'];
    }

    /**
     * Sets expire_password
     *
     * @param ?string $expire_password 
     *
     * @return $this
     */
    public function setExpirePassword($expire_password)
    {
        $this->container['expire_password'] = $expire_password;

        return $this;
    }

    /**
     * Gets expire_password_days
     *
     * @return ?string
     */
    public function getExpirePasswordDays()
    {
        return $this->container['expire_password_days'];
    }

    /**
     * Sets expire_password_days
     *
     * @param ?string $expire_password_days 
     *
     * @return $this
     */
    public function setExpirePasswordDays($expire_password_days)
    {
        $this->container['expire_password_days'] = $expire_password_days;

        return $this;
    }

    /**
     * Gets expire_password_days_metadata
     *
     * @return \DocuSign\eSign\Model\AccountPasswordExpirePasswordDays
     */
    public function getExpirePasswordDaysMetadata()
    {
        return $this->container['expire_password_days_metadata'];
    }

    /**
     * Sets expire_password_days_metadata
     *
     * @param \DocuSign\eSign\Model\AccountPasswordExpirePasswordDays $expire_password_days_metadata Metadata that indicates whether the `expirePasswordDays` property is editable.
     *
     * @return $this
     */
    public function setExpirePasswordDaysMetadata($expire_password_days_metadata)
    {
        $this->container['expire_password_days_metadata'] = $expire_password_days_metadata;

        return $this;
    }

    /**
     * Gets lockout_duration_minutes
     *
     * @return ?string
     */
    public function getLockoutDurationMinutes()
    {
        return $this->container['lockout_duration_minutes'];
    }

    /**
     * Sets lockout_duration_minutes
     *
     * @param ?string $lockout_duration_minutes 
     *
     * @return $this
     */
    public function setLockoutDurationMinutes($lockout_duration_minutes)
    {
        $this->container['lockout_duration_minutes'] = $lockout_duration_minutes;

        return $this;
    }

    /**
     * Gets lockout_duration_minutes_metadata
     *
     * @return \DocuSign\eSign\Model\AccountPasswordLockoutDurationMinutes
     */
    public function getLockoutDurationMinutesMetadata()
    {
        return $this->container['lockout_duration_minutes_metadata'];
    }

    /**
     * Sets lockout_duration_minutes_metadata
     *
     * @param \DocuSign\eSign\Model\AccountPasswordLockoutDurationMinutes $lockout_duration_minutes_metadata Metadata that indicates whether the `lockoutDurationMinutes` property is editable.
     *
     * @return $this
     */
    public function setLockoutDurationMinutesMetadata($lockout_duration_minutes_metadata)
    {
        $this->container['lockout_duration_minutes_metadata'] = $lockout_duration_minutes_metadata;

        return $this;
    }

    /**
     * Gets lockout_duration_type
     *
     * @return ?string
     */
    public function getLockoutDurationType()
    {
        return $this->container['lockout_duration_type'];
    }

    /**
     * Sets lockout_duration_type
     *
     * @param ?string $lockout_duration_type 
     *
     * @return $this
     */
    public function setLockoutDurationType($lockout_duration_type)
    {
        $this->container['lockout_duration_type'] = $lockout_duration_type;

        return $this;
    }

    /**
     * Gets lockout_duration_type_metadata
     *
     * @return \DocuSign\eSign\Model\AccountPasswordLockoutDurationType
     */
    public function getLockoutDurationTypeMetadata()
    {
        return $this->container['lockout_duration_type_metadata'];
    }

    /**
     * Sets lockout_duration_type_metadata
     *
     * @param \DocuSign\eSign\Model\AccountPasswordLockoutDurationType $lockout_duration_type_metadata Metadata that indicates whether the `lockoutDurationType` property is editable.
     *
     * @return $this
     */
    public function setLockoutDurationTypeMetadata($lockout_duration_type_metadata)
    {
        $this->container['lockout_duration_type_metadata'] = $lockout_duration_type_metadata;

        return $this;
    }

    /**
     * Gets minimum_password_age_days
     *
     * @return ?string
     */
    public function getMinimumPasswordAgeDays()
    {
        return $this->container['minimum_password_age_days'];
    }

    /**
     * Sets minimum_password_age_days
     *
     * @param ?string $minimum_password_age_days 
     *
     * @return $this
     */
    public function setMinimumPasswordAgeDays($minimum_password_age_days)
    {
        $this->container['minimum_password_age_days'] = $minimum_password_age_days;

        return $this;
    }

    /**
     * Gets minimum_password_age_days_metadata
     *
     * @return \DocuSign\eSign\Model\AccountPasswordMinimumPasswordAgeDays
     */
    public function getMinimumPasswordAgeDaysMetadata()
    {
        return $this->container['minimum_password_age_days_metadata'];
    }

    /**
     * Sets minimum_password_age_days_metadata
     *
     * @param \DocuSign\eSign\Model\AccountPasswordMinimumPasswordAgeDays $minimum_password_age_days_metadata Metadata that indicates whether the `minimumPasswordAgeDays` property is editable.
     *
     * @return $this
     */
    public function setMinimumPasswordAgeDaysMetadata($minimum_password_age_days_metadata)
    {
        $this->container['minimum_password_age_days_metadata'] = $minimum_password_age_days_metadata;

        return $this;
    }

    /**
     * Gets minimum_password_length
     *
     * @return ?string
     */
    public function getMinimumPasswordLength()
    {
        return $this->container['minimum_password_length'];
    }

    /**
     * Sets minimum_password_length
     *
     * @param ?string $minimum_password_length 
     *
     * @return $this
     */
    public function setMinimumPasswordLength($minimum_password_length)
    {
        $this->container['minimum_password_length'] = $minimum_password_length;

        return $this;
    }

    /**
     * Gets minimum_password_length_metadata
     *
     * @return \DocuSign\eSign\Model\AccountMinimumPasswordLength
     */
    public function getMinimumPasswordLengthMetadata()
    {
        return $this->container['minimum_password_length_metadata'];
    }

    /**
     * Sets minimum_password_length_metadata
     *
     * @param \DocuSign\eSign\Model\AccountMinimumPasswordLength $minimum_password_length_metadata Metadata that indicates whether the `minimumPasswordLength` property is editable.
     *
     * @return $this
     */
    public function setMinimumPasswordLengthMetadata($minimum_password_length_metadata)
    {
        $this->container['minimum_password_length_metadata'] = $minimum_password_length_metadata;

        return $this;
    }

    /**
     * Gets password_include_digit
     *
     * @return ?string
     */
    public function getPasswordIncludeDigit()
    {
        return $this->container['password_include_digit'];
    }

    /**
     * Sets password_include_digit
     *
     * @param ?string $password_include_digit 
     *
     * @return $this
     */
    public function setPasswordIncludeDigit($password_include_digit)
    {
        $this->container['password_include_digit'] = $password_include_digit;

        return $this;
    }

    /**
     * Gets password_include_digit_or_special_character
     *
     * @return ?string
     */
    public function getPasswordIncludeDigitOrSpecialCharacter()
    {
        return $this->container['password_include_digit_or_special_character'];
    }

    /**
     * Sets password_include_digit_or_special_character
     *
     * @param ?string $password_include_digit_or_special_character 
     *
     * @return $this
     */
    public function setPasswordIncludeDigitOrSpecialCharacter($password_include_digit_or_special_character)
    {
        $this->container['password_include_digit_or_special_character'] = $password_include_digit_or_special_character;

        return $this;
    }

    /**
     * Gets password_include_lower_case
     *
     * @return ?string
     */
    public function getPasswordIncludeLowerCase()
    {
        return $this->container['password_include_lower_case'];
    }

    /**
     * Sets password_include_lower_case
     *
     * @param ?string $password_include_lower_case 
     *
     * @return $this
     */
    public function setPasswordIncludeLowerCase($password_include_lower_case)
    {
        $this->container['password_include_lower_case'] = $password_include_lower_case;

        return $this;
    }

    /**
     * Gets password_include_special_character
     *
     * @return ?string
     */
    public function getPasswordIncludeSpecialCharacter()
    {
        return $this->container['password_include_special_character'];
    }

    /**
     * Sets password_include_special_character
     *
     * @param ?string $password_include_special_character 
     *
     * @return $this
     */
    public function setPasswordIncludeSpecialCharacter($password_include_special_character)
    {
        $this->container['password_include_special_character'] = $password_include_special_character;

        return $this;
    }

    /**
     * Gets password_include_upper_case
     *
     * @return ?string
     */
    public function getPasswordIncludeUpperCase()
    {
        return $this->container['password_include_upper_case'];
    }

    /**
     * Sets password_include_upper_case
     *
     * @param ?string $password_include_upper_case 
     *
     * @return $this
     */
    public function setPasswordIncludeUpperCase($password_include_upper_case)
    {
        $this->container['password_include_upper_case'] = $password_include_upper_case;

        return $this;
    }

    /**
     * Gets password_strength_type
     *
     * @return ?string
     */
    public function getPasswordStrengthType()
    {
        return $this->container['password_strength_type'];
    }

    /**
     * Sets password_strength_type
     *
     * @param ?string $password_strength_type 
     *
     * @return $this
     */
    public function setPasswordStrengthType($password_strength_type)
    {
        $this->container['password_strength_type'] = $password_strength_type;

        return $this;
    }

    /**
     * Gets password_strength_type_metadata
     *
     * @return \DocuSign\eSign\Model\AccountPasswordStrengthType
     */
    public function getPasswordStrengthTypeMetadata()
    {
        return $this->container['password_strength_type_metadata'];
    }

    /**
     * Sets password_strength_type_metadata
     *
     * @param \DocuSign\eSign\Model\AccountPasswordStrengthType $password_strength_type_metadata Metadata that indicates whether the `passwordStrengthType` property is editable.
     *
     * @return $this
     */
    public function setPasswordStrengthTypeMetadata($password_strength_type_metadata)
    {
        $this->container['password_strength_type_metadata'] = $password_strength_type_metadata;

        return $this;
    }

    /**
     * Gets questions_required
     *
     * @return ?string
     */
    public function getQuestionsRequired()
    {
        return $this->container['questions_required'];
    }

    /**
     * Sets questions_required
     *
     * @param ?string $questions_required 
     *
     * @return $this
     */
    public function setQuestionsRequired($questions_required)
    {
        $this->container['questions_required'] = $questions_required;

        return $this;
    }

    /**
     * Gets questions_required_metadata
     *
     * @return \DocuSign\eSign\Model\AccountPasswordQuestionsRequired
     */
    public function getQuestionsRequiredMetadata()
    {
        return $this->container['questions_required_metadata'];
    }

    /**
     * Sets questions_required_metadata
     *
     * @param \DocuSign\eSign\Model\AccountPasswordQuestionsRequired $questions_required_metadata Metadata that indicates whether the `questionsRequired` property is editable.
     *
     * @return $this
     */
    public function setQuestionsRequiredMetadata($questions_required_metadata)
    {
        $this->container['questions_required_metadata'] = $questions_required_metadata;

        return $this;
    }
    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}

