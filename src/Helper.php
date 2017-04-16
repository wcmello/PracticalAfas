<?php
/**
 * This file is part of the PracticalAfas package.
 *
 * (c) Roderik Muit <rm@wyz.biz>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace PracticalAfas;

use InvalidArgumentException;

/**
 * A collection of standalone helper methods for AFAS data manipulation.
 *
 * These are mainly useful before sending data into AFAS. It's split out from
 * AfasSoapConnection to protect the stability of the AfasSoapConnection
 * 'interface' a bit: this collection will probably see change over time.
 * (Plus: the data definitions in here are just too big for AfasSoapConnection.)
 *
 * If someone defined custom fields in their AFAS environment, they probably
 * want to extend xmlTypeInfo() in a subclass.
 *
 * All methods are static so far.
 */
class Helper
{
    /**
     * Maps ISO to AFAS country code.
     * (Note: this function is not complete yet, it only does Europe correctly.)
     *
     * @param string $isocode
     *   ISO9166 2-letter country code
     *
     * @return string
     *   AFAS country code
     */
    public static function convertIsoCountryCode($isocode)
    {
        // European codes we know to NOT match the 2-letter ISO codes:
        $cc = [
            'AT' => 'A',
            'BE' => 'B',
            'DE' => 'D',
            'ES' => 'E',
            'FI' => 'FIN',
            'FR' => 'F',
            'HU' => 'H',
            'IT' => 'I',
            'LU' => 'L',
            'NO' => 'N',
            'PT' => 'P',
            'SE' => 'S',
            'SI' => 'SLO',
        ];
        if (!empty($cc[strtoupper($isocode)])) {
            return $cc[strtoupper($isocode)];
        }
        // Return the input string (uppercased), or '' if the code is unknown.
        return self::convertCountryName($isocode, 1);
    }

    /**
     * Maps country name to AFAS country code.
     *
     * @param string $name
     *   Country name
     * @param int $default_behavior
     *   Code for default behavior if name is not found:
     *   0: always return empty string
     *   1: if $name itself is equal to a country code, return that code (always
     *      uppercased). So the function accepts codes as well as names.
     *   2: return the (non-uppercased) original string as default, even though
     *      it is apparently not a legal code.
     *   3: 1 + 2.
     *   4: return NL instead of '' as the default. (Because AFAS is used in NL
     *      primarily.)
     *   5: 1 + 4.
     *
     * @return string
     *   Country name, or NL / '' if not found.
     */
    public static function convertCountryName($name, $default_behavior = 0)
    {
        // We define a flipped array here because it looks nicer / I just don't want
        // to bother changing it around :p. In the future we could have this array
        // map multiple names to the same country code, in which case we need to
        // flip the keys/values.
        $codes = array_flip(array_map('strtolower', [
            'AFG' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'ASM' => 'American Samoa',
            'AND' => 'Andorra',
            'AO' => 'Angola',
            'AIA' => 'Anguilla',
            'AG' => 'Antigua and Barbuda',
            'RA' => 'Argentina',
            'AM' => 'Armenia',
            'AUS' => 'Australia',
            'A' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BRN' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BDS' => 'Barbados',
            'BY' => 'Belarus',
            'B' => 'België',
            'BH' => 'Belize',
            'BM' => 'Bermuda',
            'DY' => 'Benin',
            'BT' => 'Bhutan',
            'BOL' => 'Bolivia',
            'BA' => 'Bosnia and Herzegowina',
            'RB' => 'Botswana',
            'BR' => 'Brazil',
            'BRU' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BU' => 'Burkina Faso',
            'RU' => 'Burundi',
            'K' => 'Cambodia',
            'TC' => 'Cameroon',
            'CDN' => 'Canada',
            'CV' => 'Cape Verde',
            'RCA' => 'Central African Republic',
            'TD' => 'Chad',
            'RCH' => 'Chile',
            'CN' => 'China',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'RCB' => 'Congo',
            'CR' => 'Costa Rica',
            'CI' => 'Cote D\'Ivoire',
            'HR' => 'Croatia',
            'C' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJI' => 'Djibouti',
            'WD' => 'Dominica',
            'DOM' => 'Dominican Republic',
            'TLS' => 'East Timor',
            'EC' => 'Ecuador',
            'ET' => 'Egypt',
            'EL' => 'El Salvador',
            'CQ' => 'Equatorial Guinea',
            'ERI' => 'Eritrea',
            'EE' => 'Estonia',
            'ETH' => 'Ethiopia',
            'FLK' => 'Falkland Islands (Malvinas)',
            'FRO' => 'Faroe Islands',
            'FJI' => 'Fiji',
            'FIN' => 'Finland',
            'F' => 'France',
            'GF' => 'French Guiana',
            'PYF' => 'French Polynesia',
            'ATF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'WAG' => 'Gambia',
            'GE' => 'Georgia',
            'D' => 'Germany',
            'GH' => 'Ghana',
            'GIB' => 'Gibraltar',
            'GR' => 'Greece',
            'GRO' => 'Greenland',
            'WG' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GUM' => 'Guam',
            'GCA' => 'Guatemala',
            'GN' => 'Guinea',
            'GW' => 'Guinea-bissau',
            'GUY' => 'Guyana',
            'RH' => 'Haiti',
            'HMD' => 'Heard and Mc Donald Islands',
            'HON' => 'Honduras',
            'HK' => 'Hong Kong',
            'H' => 'Hungary',
            'IS' => 'Iceland',
            'IND' => 'India',
            'RI' => 'Indonesia',
            'IR' => 'Iran (Islamic Republic of)',
            'IRQ' => 'Iraq',
            'IRL' => 'Ireland',
            'IL' => 'Israel',
            'I' => 'Italy',
            'JA' => 'Jamaica',
            'J' => 'Japan',
            'HKJ' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'EAK' => 'Kenya',
            'KIR' => 'Kiribati',
            'KO' => 'Korea, Democratic People\'s Republic of',
            'ROK' => 'Korea, Republic of',
            'KWT' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LAO' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'RL' => 'Lebanon',
            'LS' => 'Lesotho',
            'LB' => 'Liberia',
            'LAR' => 'Libyan Arab Jamahiriya',
            'FL' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'L' => 'Luxembourg',
            'MO' => 'Macau',
            'MK' => 'Macedonia, The Former Yugoslav Republic of',
            'RM' => 'Madagascar',
            'MW' => 'Malawi',
            'MAL' => 'Malaysia',
            'MV' => 'Maldives',
            'RMM' => 'Mali',
            'M' => 'Malta',
            'MAR' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'RIM' => 'Mauritania',
            'MS' => 'Mauritius',
            'MYT' => 'Mayotte',
            'MEX' => 'Mexico',
            'MIC' => 'Micronesia, Federated States of',
            'MD' => 'Moldova, Republic of',
            'MC' => 'Monaco',
            'MON' => 'Mongolia',
            'MSR' => 'Montserrat',
            'MA' => 'Morocco',
            'MOC' => 'Mozambique',
            'BUR' => 'Myanmar',
            'SWA' => 'Namibia',
            'NR' => 'Nauru',
            'NL' => 'Nederland',
            'NPL' => 'Nepal',
            'NA' => 'Netherlands Antilles',
            'NCL' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NIC' => 'Nicaragua',
            'RN' => 'Niger',
            'WAN' => 'Nigeria',
            'NIU' => 'Niue',
            'NFK' => 'Norfolk Island',
            'MNP' => 'Northern Mariana Islands',
            'N' => 'Norway',
            'OMA' => 'Oman',
            'PK' => 'Pakistan',
            'PLW' => 'Palau',
            'PSE' => 'Palestina',
            'PA' => 'Panama',
            'PNG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'RP' => 'Philippines',
            'PCN' => 'Pitcairn',
            'PL' => 'Poland',
            'P' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'REU' => 'Reunion',
            'RO' => 'Romania',
            'RUS' => 'Russian Federation',
            'RWA' => 'Rwanda',
            'KN' => 'Saint Kitts and Nevis',
            'WL' => 'Saint Lucia',
            'WV' => 'Saint Vincent and the Grenadines',
            'WSM' => 'Samoa',
            'RSM' => 'San Marino',
            'ST' => 'Sao Tome and Principe',
            'AS' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'SRB' => 'Serbia',
            'SY' => 'Seychelles',
            'WAL' => 'Sierra Leone',
            'SGP' => 'Singapore',
            'SK' => 'Slovakia (Slovak Republic)',
            'SLO' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SP' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'E' => 'Spain',
            'CL' => 'Sri Lanka',
            'SHN' => 'St. Helena',
            'SPM' => 'St. Pierre and Miquelon',
            'SUD' => 'Sudan',
            'SME' => 'Suriname',
            'SJM' => 'Svalbard and Jan Mayen Islands',
            'SD' => 'Swaziland',
            'S' => 'Sweden',
            'CH' => 'Switzerland',
            'SYR' => 'Syrian Arab Republic',
            'RC' => 'Taiwan',
            'TAD' => 'Tajikistan',
            'EAT' => 'Tanzania, United Republic of',
            'T' => 'Thailand',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TMN' => 'Turkmenistan',
            'TCA' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'EAU' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'USA' => 'United States',
            'UMI' => 'United States Minor Outlying Islands',
            'ROU' => 'Uruguay',
            'OEZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VAT' => 'Vatican City State (Holy See)',
            'YV' => 'Venezuela',
            'VN' => 'Viet Nam',
            'VGB' => 'Virgin Islands (British)',
            'VIR' => 'Virgin Islands (U.S.)',
            'WLF' => 'Wallis and Futuna Islands',
            'ESH' => 'Western Sahara',
            'YMN' => 'Yemen',
            'Z' => 'Zambia',
            'ZW' => 'Zimbabwe',
        ]));

        if (isset($codes[strtolower($name)])) {
            return $codes[$name];
        }
        if ($default_behavior | 1) {
            // Search for code inside array. If found, $name is a code.
            if (in_array(strtoupper($name), $codes, true)) {
                return strtoupper($name);
            }
        }
        if ($default_behavior | 2) {
            return $name;
        }
        if ($default_behavior | 4) {
            return 'NL';
        }
        return '';
    }

    /**
     * Checks if a string can be interpreted as a valid Dutch phone number.
     * (There's only a "Dutch" function since AFAS will have 99% Dutch clients.
     * Extended helper functionality can be added as needed.)
     *
     * @param string $phonenumber
     *   Phone number to be validated.
     *
     * @return array
     *   If not recognized, empty array. If recognized: 2-element array with
     *     area/ mobile code and local part - as input; not uniformly
     *     re-formatted yet.
     */
    public static function validateDutchPhoneNr($phonenumber)
    {
        /*
          Accepts:
              06-12345678
              06 123 456 78
              010-1234567
              +31 10-1234567
              +31-10-1234567
              +31 (0)10-1234567
              +3110-1234567
              020 123 4567
              (020) 123 4567
              0221-123456
              0221 123 456
              (0221) 123 456
          Rejects:
              010-12345678
              05-12345678
              061-2345678
              (06) 12345678
              123-4567890
              123 456 7890
              +31 010-1234567
        */

        // Area codes start with 0, +31 or the (now deprecated) '+31 (0)'.
        // Non-mobile area codes starting with 0 may be surrounded by brackets.
        foreach (
            [
                '((?:\+31[-\s]?(?:\(0\))?\s?|0)6)            # mobile
        [-\s]* ([1-9]\s*(?:[0-9]\s*){7})',

                '((?:\+31[-\s]?(?:\(0\))?\s?|0)[1-5789][0-9] # 3-digit area code...
        | \(0[1-5789][0-9]\))                        # (possibly between brackets...)
        [-\s]* ([1-9]\s*(?:[0-9]\s*){6})             # ...plus local number.',

                '((?:\+31[-\s]?(?:\(0\))?\s?|0)[1-5789][0-9]{2} # 4-digit area code...
        |\(0[1-5789][0-9]{2}\))                         # (possibly between brackets...)
        [-\s]* ([1-9]\s*(?:[0-9]\s*){5})                # ...plus local number.',
            ] as $regex) {

            if (preg_match('/^\s*' . $regex . '\s*$/x', $phonenumber, $matches)) {
                $return = [
                    strtr($matches[1], [' ' => '', '-' => '', '+31' => '0']),
                    $matches[2],
                ];
                // $return[0] is a space-less area code now, with or without trailing 0.
                // $return[1] is not formatted.
                if ($return[0][0] !== '0') {
                    $return[0] = "0$return[0]";
                }
                return $return;
            }
        }
        return [];
    }

    /**
     * Normalizes country_code, last_name, extracts search_name for use in
     * update connectors. This function can be called for an array containing
     * person data, address data, or both. See code details; it contains 'Dutch
     * specific' logic, which can be a nice time saver but is partly arbitrary
     * and not necessarily complete.
     *
     * This function only works if the keys in $data are all aliases (like
     * first_name), not original AFAS tag names (like FiNm)!
     *
     * Phone number reformatting has not been incorporated into this function,
     * because there is no uniform standard for it. (The 'official' standard
     * of (012) 3456789 is not what most people want, i.e. 012-3456789.) You'll
     * need to do this yourself additionally, using validateDutchPhoneNr().
     *
     * @param $data
     *   Array with person and/or address data.
     */
    public static function normalizePersonAddress(&$data)
    {

        if (!empty($data['country_code'])) {
            // country_code can contain names as well as ISO9166 country codes;
            // normalize it to AFAS code.
            // NOTE: country_code is assumed NOT to contain an AFAS 1/3 letter country
            // code (because who uses these anyway?); these would be emptied out!
            if (strlen($data['country_code']) > 3) {
                $data['country_code'] = self::convertCountryName($data['country_code'], 3);
            } else {
                $data['country_code'] = self::convertIsoCountryCode($data['country_code']);
            }
        }

        $matches = [];
        if (!empty($data['street']) && empty($data['house_number']) &&
            empty($data['house_number_ext'])
            // Split off house number and possible extension from street,
            // because AFAS has separate fields for those. We do this _only_ for
            // defined countries where the splitting of house numbers is common.
            // (This is a judgment call, and the list of countries is arbitrary,
            // but there's slightly less risk of messing up foreign addresses
            // that way.) 'No country' is assumed to be 'NL' since AFAS is
            // NL-centric.
            // This code comes from addressfield_tfnr module and was adjusted
            // later to conform to AFAS' definition of "extension".
            && (empty($data['country_code']) || in_array($data['country_code'],
                    ['B', 'D', 'DK', 'F', 'FIN', 'H', 'NL', 'NO', 'S']))
            && preg_match('/^
          (.*?\S) \s+ (\d+) # normal thoroughfare, followed by spaces and a number;
                            # non-greedy because for STREET NR1 NR2, "nr1" should
                            # end up in the number field, not "nr2".
          (?:\s+)?          # optionally separated by spaces
          (\S.{0,29})?      # followed by optional suffix of at most 30 chars (30 is the maximum in the AFAS UI)
          \s* $/x', $data['street'], $matches)
        ) { // x == extended regex pattern
            // More notes about the pattern:
            // - theoretically a multi-digit number could be split into
            //   $matches[2/3]; this does not happen because the 3rd match is
            //   non-greedy.
            // - for numbers like 2-a and 2/a, we include the -// into
            //   $matches[3] on purpose: if AFAS has suffix "-a" or "/a" it
            //   prints them like "2-a" or "2/a" when printing an address. On
            //   the other hand, if AFAS has suffix "a" or "3", it prints them
            //   like "2 a" or "2 3".
            $data['street'] = ltrim($matches[1]);
            $data['house_number'] = $matches[2];
            if (!empty($matches[3])) {
                $data['house_number_ext'] = rtrim($matches[3]);
            }
        } elseif (!empty($data['house_number']) && empty($data['house_number_ext'])) {
            // Split off extension from house number
            $matches = [];
            if (preg_match('/^ \s* (\d+) (?:\s+)? (\S.{0,29})? \s* $/x', $data['house_number'], $matches)) {
                // Here too, the last ? means $matches[2] may be empty, but
                // prevents a multi-digit number from being split into
                // $matches[1/2].
                if (!empty($matches[2])) {
                    $data['house_number'] = $matches[1];
                    $data['house_number_ext'] = rtrim($matches[2]);
                }
            }
        }

        if (!empty($data['last_name']) && empty($data['prefix'])) {
            // Split off (Dutch) prefix from last name.
            // NOTE: creepily hardcoded stuff. Spaces are necessary, and sometimes
            // ordering matters! ('van de' before 'van')
            $name = strtolower($data['last_name']);
            foreach ([
                         'de ',
                         'v.',
                         'v ',
                         'v/d ',
                         'v.d.',
                         'van de ',
                         'van der ',
                         'van ',
                         "'t "
                     ] as $value) {
                if (strpos($name, $value) === 0) {
                    $data['prefix'] = rtrim($value);
                    $data['last_name'] = trim(substr($data['last_name'], strlen($value)));
                    break;
                }
            }
        }

        // Set search name
        if (!empty($data['last_name']) && empty($data['search_name'])) {
            // Zoeknaam: we got no request for a special definition of this, so:
            $data['search_name'] = strtoupper($data['last_name']);
            // Max length is 10, and we don't need to be afraid of duplicates.
            if (strlen($data['search_name']) > 10) {
                $data['search_name'] = substr($data['search_name'], 0, 10);
            }
        }

        if (!empty($data['first_name']) && empty($data['initials'])) {
            $data['first_name'] = trim($data['first_name']);

            // Check if first name is really only initials. If so, move it.
            // AFAS' automatic resolving code in its new-(contact)person UI
            // thinks anything is initials if it contains a dot. It will thenx
            // prevents a place spaces in between every letter, but we won't do
            // that last part. (It may be good for user UI input, but coded data
            // does not expect it.)
            if (strlen($data['first_name']) == 1
                || strlen($data['first_name']) < 16
                && strpos($data['first_name'], '.') !== false
                && strpos($data['first_name'], ' ') === false
            ) {
                // Dot but no spaces, or just one letter: all initials; move it.
                $data['initials'] = strlen($data['first_name']) == 1 ?
                    strtoupper($data['first_name']) . '.' : $data['first_name'];
                unset($data['first_name']);
            } elseif (preg_match('/^[A-Za-z \-]+$/', $data['first_name'])) {
                // First name only contains letters, spaces and hyphens. In this
                // case (which is probeably stricter than the AFAS UI), create
                // initials.
                $data['initials'] = '';
                foreach (preg_split('/[- ]+/', $data['first_name']) as $part) {
                    // Don't separate initials by spaces, only dot.
                    $data['initials'] .= strtoupper(substr($part, 0, 1)) . '.';
                }
            }
            // Note if there's both a dot and spaces in 'first_name' we skip it.
        }
    }

    /**
     * Construct XML representing one or more AFAS 'items'.
     *
     * xmlTypeInfo() is an evolving function containing lots of (maybe
     * incomplete) hardcoded logic and comment fragments from incomplete info
     * in AFAS' knowledge base. Because of this volatility, $data must adhere
     * to a strict structure; the function will throw exceptions when e.g.
     * required data is not present, present data is not recognized, ...
     *
     * Connection::getData(CONNECTOR, [], 'data') can be used for getting XSD
     * schema info to make this function more robust. The information from those
     * XSD schemas may be more complete, so if you want to use those  schemas to
     * construct XML instead of using this function, that's fine. This function
     * exists for those who would like to construct array data with more
     * descriptive keys, instead of an XML string with hard to remember tags.
     *
     * We hope that the below code catches all strange/dangerous combinations of
     * 'id' /  $fields_action / AutoNum / MatchXXX values and 'embedding items',
     * and made enough comments in xmlTypeInfo() to explain AFAS' behavior. But
     * we can't be totally sure. Please read the comments at MatchPer/MachOga
     * details before dealing with knPerson/knOrganisation objects; it may save
     * lots of time and wrong assumptions.
     *
     * @param string $type
     *   The type of item; this (usually?) corresponds to the outer tag in the
     *   XML string / an 'updateConnectorId'; see xmlTypeInfo() for possible
     *   values. An exception is thrown if the type is unknown.
     * @param array $data
     *   Data to construct the XML from; see xmlTypeInfo() for possible values.
     *   This can represent a set of (one or more) items; in this case all
     *   values are arrays representing a single item. It can also represent one
     *   item, in which case all keys in this array are XML tags or aliases and
     *   values are the corresponding tag/field (scalar) or related-object
     *   (array) value. The item must contain at least one scalar (field) value;
     *   passing one data object containing only related-objects and no fields
     *   is disallowed. (If you have to, make it a 'set of 1 item' by wrapping
     *   it in [].)
     *   An item can have two other key-value pairs that do not represent real
     *   item values:
     *   - #id: the 'id attribute' of an item in XML. Only some item types have
     *     'id attributes'. (Most have id numbers in separate tags).
     *   - #action: the 'fields action' (see $fields_action) to perform for a
     *     nested item, if that is different from $fields_action. This must
     *     never be set for an 'outer' item; use $fields_action parameter
     *     instead.
     * @param string $fields_action
     *   (optional) Action to fill in 'Fields' tag; can be "insert", "update",
     *   "delete", "". In cases where specifying "insert" in the XML does not
     *   make AFAS' behavior different from not specifying anything, it may
     *   still be important because this method will only add default field
     *   values if "insert" is explicitly specified.
     *   Combination of $fields_action "insert" and non-empty id is allowed
     *   (probably you have autonumbering turned off, if you do this).
     *   Combination of $fields_action "update" and empty id is logical only
     *   when the item has a 'MatchXXX' property; see xmlTypeInfo().
     * @param string $parent_type
     *   (optional) If nonempty, the generated XML will be a fragment suitable
     *   for embedding within the parent type, which is slightly different from
     *   standalone XML.
     * @param int $indent
     *   (optional) Add spaces before each tag and end each line except the last
     *   one with newline, unless $indent < 0 (then do not add anything).
     *
     * @return string
     *   The constructed XML, which is suitable for sending through an AFAS
     *   UpdateConnector.. All values in $data get XML-encoded/escaped.
     *
     * @throws \InvalidArgumentException
     *   If arguments have an unrecognized / invalid format.
     *
     * @see xmlTypeInfo()
     */
    public static function constructXml($type, $data, $fields_action = '', $parent_type = '', $indent = -1)
    {
        if (!in_array($fields_action, ['insert', 'update', 'delete', ''], true)) {
            throw new InvalidArgumentException("Unknown value $fields_action for fields_action parameter.");
        }

        // We set tab width in a variable even though we never change it.
        $tab_width = 2;

        // Item header
        $xml = '';
        $indent_str = '';
        $extra_spaces = '';
        if ($indent >= 0) {
            // This will be used to add $tab_width spaces to $indent_str, each
            // time we enter inside a tag. See below.
            $extra_spaces = str_repeat(' ', $tab_width);

            // This is the initial value _after_ the outer XML line. While being
            // built, the last line in $xml will not end with a newline:
            $indent_str = "\n" . str_repeat(' ', $indent + $tab_width);

            $xml = str_repeat(' ', $indent);
        }
        $xml .= '<' . $type . ($parent_type ? '>' : ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">');

        // Determine if $element holds a single item or an array of items:
        // if one item, we must have at least one scalar value; if several, all
        // values must be arrays.
        foreach ($data as $key => $element) {
            if (is_scalar($element)) {
                // Normalize $data to an array of items.
                $data = [$data];
                break;
            }
        }

        foreach ($data as $key => $element) {

            // Construct element with fields within the XML. (For each element
            // inside the loop, because $info can differ with $element.)
            $info = static::xmlTypeInfo($type, $parent_type, $element, $fields_action);
            if (empty($info)) {
                throw new InvalidArgumentException("No XML definition present for item type $type.");
            }
            /* About the field definitions:
             * - if required = true and default is given, then
             *   - the default value is sent if no data value is passed
             *   - an exception is (only) thrown if the passed value is null.
             * - if the default is null (or value given is null & not
             *   'required') then <$tag xsi:nil=\"true\"/> is passed.
             */

            // Action and Id are set inside 'fake properties' in the data array;
            // use them and unset them.
            if (isset($element['#action'])) {
                if (empty($parent_type)) {
                    // This really is an override and we want to keep it that
                    // way. When possible, people must specify a correct
                    // $fields_action.
                    throw new InvalidArgumentException('#action override is only allowed in embedded objects.');
                }
                // Not sure whether '' makes sense as an override?
                // Also maybe we should disallow deletes inside inserts etc?
                if (!in_array($element['#action'], ['insert', 'update', 'delete'], true)) {
                    throw new InvalidArgumentException("Unknown value {$element['#action']} for #action inside $type.");
                }
                $action = $element['#action'];
                unset($element['#action']);
            } else {
                $action = $fields_action;
            }

            if (!empty($element['#id']) && empty($info['id_field'])) {
                throw new InvalidArgumentException("Id value provided but no id-field defined for item $type.");
            }

            $xml .= $indent_str . '<Element'
                 . (empty($element['#id']) ? '' : ' ' . $info['id_field'] . '="' . $element['#id'] . '"')
                 . '>';
            unset($element['#id']);
            $indent_str .= $extra_spaces;

            $xml .= $indent_str . '<Fields' . ($action ? " Action=\"$action\"" : '') . '>';

            // Convert all of our item data into tags, check required tags, and add
            // default values for tags (where defined).
            foreach ($info['fields'] as $tag => $map_properties) {
                $value_present = true;

                // Get value from the property equal to the tag (case
                // sensitive!), or the alias. If two values are present with
                // both tag and alias, we throw an exception.
                $value_exists_by_alias = isset($map_properties['alias']) && array_key_exists($map_properties['alias'], $element);
                if (array_key_exists($tag, $element)) {
                    if ($value_exists_by_alias) {
                        throw new InvalidArgumentException("Item $type has a value provided by both its property name $tag and alias $map_properties[alias].");
                    }
                    $value = $element[$tag];
                    unset($element[$tag]);
                } elseif ($value_exists_by_alias) {
                    $value = $element[$map_properties['alias']];
                    unset($element[$map_properties['alias']]);
                } elseif (array_key_exists('default', $map_properties)) {
                    $value = $map_properties['default'];
                } else {
                    $value_present = false;
                }

                // Required fields will disallow non-passed values, or passed
                // null values.
                if (!empty($map_properties['required'])
                    && (!$value_present || !isset($value))
                ) {
                    $property = $tag . (isset($map_properties['alias']) ? " ({$map_properties['alias']})" : '');
                    throw new InvalidArgumentException("No value given for item $type, required property $property.");
                }

                if ($value_present) {
                    if (isset($value) && !empty($map_properties['type'])) {
                        switch ($map_properties['type']) {
                            case 'boolean':
                                $value = $value ? '1' : '0';
                                break;
                            case 'long':
                            case 'decimal':
                                if (!is_numeric($value)) {
                                    $property = $tag . (isset($map_properties['alias']) ? " ({$map_properties['alias']})" : '');
                                    throw new InvalidArgumentException('Property $property in item $type must be numeric.');
                                }
                                if ($map_properties['type'] === 'long' && strpos((string)$value, '.') !== false) {
                                    $property = $tag . (isset($map_properties['alias']) ? " ({$map_properties['alias']})" : '');
                                    throw new InvalidArgumentException("Property $property in item $type must be a 'long'.");
                                }
                                // For decimal, we could also check digits, but
                                // we're not going that far yet.
                                break;
                            case 'date':
                                // @todo format in standard way, once we know that's necessary
                                break;
                        }
                    }
                    $xml .= $indent_str . $extra_spaces . (isset($value)
                            ? "<$tag>" . static::xmlValue($value) . "</$tag>"
                            // Value is passed but null or default value is null
                            : "<$tag xsi:nil=\"true\"/>");
                }
            }
            $xml .= $indent_str . '</Fields>';

            if (!empty($element)) {
                // Add other embedded objects. (We assume all remaining element
                // values are indeed objects. If not, an error will be thrown.)
                $xml .= $indent_str . '<Objects>';

                foreach ($info['objects'] as $tag => $alias) {
                    $value_present = true;

                    // Get value from the property equal to the tag (case
                    // sensitive!), or the alias. If two values are present with
                    // both tag and alias, we throw an exception.
                    if (array_key_exists($tag, $element)) {
                        if (array_key_exists($alias, $element)) {
                            throw new InvalidArgumentException("Item $type has a value provided by both its property name $tag and alias $alias.");
                        }
                        $value = $element[$tag];
                        unset($element[$tag]);
                    } elseif (array_key_exists($alias, $element)) {
                        $value = $element[$alias];
                        unset($element[$alias]);
                    } else {
                        $value_present = false;
                    }

                    if ($value_present) {
                        if (!is_array($value)) {
                            $property = $tag . (isset($alias) ? " ($alias)" : '');
                            throw new InvalidArgumentException("Value for object $property in item $type must be array.");
                        }
                        $xml .= ($indent < 0 ? '' : "\n") . self::constructXml(
                                $tag,
                                $value,
                                $action,
                                $type,
                                $indent < 0 ? $indent : $indent + 3 * $tab_width
                            );
                    }
                }
                $xml .= $indent_str . '</Objects>';
            }

            // Throw error for unknown item data (for which we have not seen a tag).
            if (!empty($element)) {
                $keys = "'" . implode(', ', array_keys($element)) . "'";
                throw new InvalidArgumentException("Unmapped data values provided for item type $type: keys are $keys.");
            }

            // Add closing XML tags.
            if ($indent >= 0) {
                $xml .= "\n" . str_repeat(' ', $indent + $tab_width) . '</Element>';
            } else {
                $xml .= '</Element>';
            }
        }

        // Add closing XML tag.
        if ($indent >= 0) {
            // Do not end the whole string with newline.
            $xml .= "\n" . str_repeat(' ', $indent) . "</$type>";
        } else {
            $xml .= "</$type>";
        }

        return $xml;
    }

    /**
     * Prepare a value for inclusion in XML: trim and encode.
     *
     * @param string $text
     *
     * @return string
     */
    protected static function xmlValue($text)
    {
        // check_plain() / ENT_QUOTES converts single quotes to &#039; which is
        // illegal in XML so we can't use it for sanitizing.) The below is
        // equivalent to "htmlspecialchars($text, ENT_QUOTES | ENT_XML1)", but
        // also valid in PHP < 5.4.
        return str_replace("'", '&apos;', htmlspecialchars(trim($text)));
    }

    /**
     * Return info for a certain type (dataConnectorId) definition.
     *
     * @param string $type
     *   The type of item; this (usually?) corresponds to the outer tag in the
     *   XML string / an 'updateConnectorId'. The function throws an
     *   exception if the type is not known by this function.
     * @param string $parent_type
     *   If nonempty, the generated XML will be a fragment suitable for
     *   embedding within the parent type; this can influence the presence of
     *   some fields.
     * @param array $data
     *   Data to construct the XML from. This can influence e.g. some defaults.
     * @param string $fields_action
     *   Action to fill in 'fields' tag; can be "insert", "update", "delete",
     *   "". This can influence e.g. some defaults.
     *
     * @return array
     *   Array with possible keys: 'id_field', 'fields' and 'objects'. See
     *   the code.
     *
     * @see constructXml()
     */
    protected static function xmlTypeInfo($type, $parent_type, $data, $fields_action)
    {

        $inserting = $fields_action === 'insert';

        $info = [];
        switch ($type) {
            // Even though they are separate types, there is no standalone
            // updateConnector for addresses.
            case 'KnBasicAddressAdr':
            case 'KnBasicAddressPad':
                $info = [
                    'fields' => [
                        // Land (verwijzing naar: Land => AfasKnCountry)
                        'CoId' => [
                            'alias' => 'country_code',
                        ],
                        /*   PbAd = 'is postbusadres' (if True, HmNr has number of P.O. box)
                         *   Ad, HmNr, ZpCd are required.
                         *      (and a few lines below, the docs say:)
                         *   Rs is _also_ " 'essential', even if ResZip==true, because if Zip
                         *      could not be resolved, the specified value of Rs is taken."
                         *      So we'll make it required too.
                         */
                        'PbAd' => [
                            'alias' => 'is_po_box',
                            'type' => 'boolean',
                            'required' => true,
                            'default!' => false,
                        ],
                        // Toev. voor straat
                        'StAd' => [],
                        // Straat
                        'Ad' => [
                            'alias' => 'street',
                            'required' => true,
                        ],
                        // Huisnummer
                        'HmNr' => [
                            'alias' => 'house_number',
                            'type' => 'long',
                        ],
                        // Toev. aan huisnr.
                        'HmAd' => [
                            'alias' => 'house_number_ext',
                        ],
                        // Postcode
                        'ZpCd' => [
                            'alias' => 'zip_code',
                            'required' => true,
                        ],
                        // Woonplaats (verwijzing naar: Woonplaats => AfasKnResidence)
                        'Rs' => [
                            'alias' => 'town',
                            'required' => true,
                        ],
                        // Adres toevoeging
                        'AdAd' => [],
                        // From "Organisaties toevoegen en wijzigen (UpdateConnector KnOrganisation)":
                        // Bij het eerste adres (in de praktijk bij een nieuw record) hoeft u geen begindatum aan te leveren in het veld 'BeginDate' genegeerd.
                        // Als er al een adres bestaat, geeft u met 'BeginDate' de ingangsdatum van de adreswijziging aan.
                        // Ingangsdatum adreswijziging (wordt genegeerd bij eerste datum)
                        'BeginDate' => [
                            'type' => 'date',
                            'default!' => date('Y-m-d', REQUEST_TIME),
                        ],
                        'ResZip' => [
                            'alias' => 'resolve_zip',
                            'type' => 'boolean',
                            'default!' => false,
                        ],
                    ],
                ];
                break;

            case 'KnContact':
                $info = [
                    'objects' => [
                        'KnBasicAddressAdr' => 'address',
                        'KnBasicAddressPad' => 'postal_address',
                    ],
                    'fields' => [
                        // Code organisatie
                        'BcCoOga' => [],
                        // Code persoon
                        'BcCoPer' => [],
                        // Postadres is adres
                        'PadAdr' => [
                            'type' => 'boolean',
                        ],
                        // Afdeling contact
                        'ExAd' => [],
                        // Functie (verwijzing naar: Tabelwaarde,Functie contact => AfasKnCodeTableValue)
                        'ViFu' => [],
                        // Functie op visitekaart
                        'FuDs' => [
                            // Abbreviates 'function description', but that seems too Dutch.
                            'alias' => 'job_title',
                        ],
                        // Correspondentie
                        'Corr' => [
                            'type' => 'boolean',
                        ],
                        // Voorkeursmedium (verwijzing naar: Tabelwaarde,Medium voor correspondentie => AfasKnCodeTableValue)
                        'ViMd' => [],
                        // Telefoonnr. werk
                        'TeNr' => [
                            'alias' => 'phone',
                        ],
                        // Fax werk
                        'FaNr' => [
                            'alias' => 'fax',
                        ],
                        // Mobiel werk
                        'MbNr' => [
                            'alias' => 'mobile',
                        ],
                        // E-mail werk
                        'EmAd' => [
                            'alias' => 'email',
                        ],
                        // Homepage
                        'HoPa' => [
                            'alias' => 'homepage',
                        ],
                        // Toelichting
                        'Re' => [
                            'alias' => 'comment',
                        ],
                        // Geblokkeerd
                        'Bl' => [
                            'alias' => 'blocked',
                            'type' => 'boolean',
                        ],
                        // T.a.v. regel
                        'AtLn' => [],
                        // Briefaanhef
                        'LeHe' => [],
                        // Sociale netwerken
                        'SocN' => [],
                        // Facebook
                        'Face' => [
                            'alias' => 'facebook',
                        ],
                        // LinkedIn
                        'Link' => [
                            'alias' => 'linkedin',
                        ],
                        // Twitter
                        'Twtr' => [
                            'alias' => 'twitter',
                        ],
                        // Persoon toegang geven tot afgeschermde deel van de portal(s)
                        'AddToPortal' => [
                            'type' => 'boolean',
                        ],
                        // E-mail toegang
                        'EmailPortal' => [],
                    ],
                ];
                if ($parent_type === 'KnOrganisation' || $parent_type === 'KnPerson') {
                    $info['fields'] += [
                        // Soort Contact
                        // Values:  AFD:Afdeling bij organisatie   AFL:Afleveradres
                        // if inside knOrganisation: + PRS:Persoon bij organisatie (alleen mogelijk i.c.m. KnPerson tak)
                        //
                        // The description in 'parent' update connectors' (KnOrganisation, knContact) KB pages is:
                        // "Voor afleveradressen gebruikt u de waarde 'AFL': <ViKc>AFL</ViKc>"
                        'ViKc' => [
                            'alias' => 'contact_type',
                        ],
                    ];

                    // According to the XSD, a knContact can contain a knPerson
                    // if it's inside a knOrganisation, but not if it's
                    // standalone.
                    if ($parent_type === 'KnOrganisation') {
                        $info['objects']['KnPerson'] = 'person';

                        // If we specify a person in the data too, 'Persoon' is
                        // the default.
                        if (!empty($data['KnPerson']) || !empty($data['person'])) {
                            $info['fields']['ViKc']['default'] = 'PRS';
                        }
                    }

                    unset($info['fields']['BcCoOga']);
                    unset($info['fields']['BcCoPer']);
                    unset($info['fields']['AddToPortal']);
                    unset($info['fields']['EmailPortal']);
                }
                break;

            case 'KnPerson':
                $info = [
                    'objects' => [
//            'KnBankAccount' => 'bank_account',
                        'KnBasicAddressAdr' => 'address',
                        'KnBasicAddressPad' => 'postal_address',
                        'KnContact' => 'contact',
                    ],
                    'fields' => [
                        // Postadres is adres
                        'PadAdr' => [
                            'type' => 'boolean',
                        ],
                        'AutoNum' => [
                            'type' => 'boolean',
                            // See below for a dynamic default
                        ],
                        /**
                         * If you specify MatchPer and if the corresponding fields have
                         * values, the difference between $fields_action "update" and
                         * "insert" falls away: if there is a match (but only one) the
                         * existing record is updated. If there isn't, a new one is
                         * inserted. If there are multiple matches, or a wrong match method
                         * is specified, AFAS throws an error.
                         *
                         * We make sure that you must explicitly specify a value for this
                         * with $field_action "update" (and get an error if you don't), by
                         * setting the default - see further down.
                         *
                         * NOTE 20150215: updating/inserting a contact/person inside an
                         * organization is only possible by sending in an embedded
                         * knOrganisation -> knContact -> knPerson XML (as far as I know).
                         * But updating existing data is tricky.
                         * Updates-or-inserts work when specifying non-zero match_method, no
                         * BcCo numbers and no $fields_action (if there are no multiple
                         * matches; those will yield an error).
                         * Specifying MatchPer=0 and BcCo for an existing org+person, and no
                         * $fields_action, yields an AFAS error "Object variable or With
                         * block variable not set" (which is a Visual Basic error, pointing
                         * to an error in AFAS' program code). To bypass this error,
                         * $fields_action "update" must be explicitly specified.
                         * When inserting new contact/person objects into an existing
                         * organization (without risking the 'multiple matches' error above)
                         * $fields_action "update" + BcCo + MatchPer=0 must be specified for
                         * the organization, and $fields_action "insert" must be specified
                         * for the contact/person object. (In constructXML() use '#action'.)
                         *
                         * NOTE - for Qoony sources in 2011 (which inserted KnPerson objects
                         *   inside KnSalesRelationPer), 3 had the comment
                         *   "match customer by mail". They used 3 until april 2014, when
                         *   suddenly updates broke, giving "organisation vs person objects"
                         *   and "multiple person objects found for these search criteria"
                         *   errors. So apparently the official description (below) was not
                         *   accurate until 2014, and maybe the above was implemented?
                         *   While fixing the breakage, AFAS introduced an extra value for
                         *   us:
                         * 9: always update the knPerson objects (which are at this moment
                         *    referenced by the outer object) with the given data.
                         *    (When inserting instead of updating data, I guess this falls
                         *    back to behavior '7', given our usage at Qoony.)
                         */
                        // Persoon vergelijken op
                        // Values:  0:Zoek op BcCo (Persoons-ID)   1:Burgerservicenummer   2:Naam + voorvoegsel + initialen + geslacht   3:Naam + voorvoegsel + initialen + geslacht + e-mail werk   4:Naam + voorvoegsel + initialen + geslacht + mobiel werk   5:Naam + voorvoegsel + initialen + geslacht + telefoon werk   6:Naam + voorvoegsel + initialen + geslacht + geboortedatum   7:Altijd nieuw toevoegen
                        'MatchPer' => [
                            'alias' => 'match_method',
                        ],
                        // Organisatie/persoon (intern)
                        // From "Organisaties toevoegen en wijzigen (UpdateConnector KnOrganisation)":
                        // "Do not deliver the 'BcId' field."
                        // (Because it really is internal. So why should we define it?)
                        //'BcId' => [
                        //  'type' => 'long',
                        //),
                        // Nummer, 1-15 chars
                        'BcCo' => [
                            // 'ID' would be more confusing because it's not the internal ID.
                            'alias' => 'number',
                        ],
                        'SeNm' => [
                            'alias' => 'search_name',
                        ],
                        // Roepnaam
                        'CaNm' => [
                            'alias' => 'name',
                        ],
                        // Voornaam
                        'FiNm' => [
                            'alias' => 'first_name',
                            'required' => true,
                        ],
                        // initials
                        'In' => [
                            'alias' => 'initials',
                        ],
                        'Is' => [
                            'alias' => 'prefix',
                        ],
                        'LaNm' => [
                            'alias' => 'last_name',
                            'required' => true,
                        ],
                        // Geboortenaam apart vastleggen
                        'SpNm' => [
                            'type' => 'boolean',
                            'default' => false,
                        ],
                        // Voorv. geb.naam
                        'IsBi' => [],
                        // Geboortenaam
                        'NmBi' => [],
                        // Voorvoegsel partner
                        'IsPa' => [],
                        // Geb.naam partner
                        'NmPa' => [],
                        // Naamgebruik (verwijzing naar: Tabelwaarde,Naamgebruik (meisjesnaam etc.) => AfasKnCodeTableValue)
                        // Values:  0:Geboortenaam   1:Geb. naam partner + Geboortenaam   2:Geboortenaam partner   3:Geboortenaam + Geb. naam partner
                        'ViUs' => [],
                        // Sex (M = Man, V = Vrouw, O = Onbekend)
                        'ViGe' => [
                            'alias' => 'gender',
                            'default' => 'O',
                            // The default is only for explicit inserts; see below. This means
                            // that for data which is ambiguous about being an insert or
                            // update, you must specify a value yourself, otherwise you get an
                            // error "Bij een persoon is het geslacht verplicht.".
                            // There is no other way; if we set a default here for non-inserts
                            // we risk silently overwriting the gender value present in AFAS.
                        ],
                        // Nationaliteit (verwijzing naar: Tabelwaarde,Nationaliteit (NEN1888) => AfasKnCodeTableValue)
                        // Values:  000:Onbekend   NL:Nederlandse   DZ:Algerijnse   AN:Angolese   RU:Burundische   RB:Botswaanse   BU:Burger van Burkina Faso   RCA:Centrafrikaanse   KM:Comorese   RCB:Kongolese   DY:Beninse   ET:Egyptische   EQ:Equatoriaalguinese   ETH:Etiopische   DJI:Djiboutiaanse   GA:Gabonese   WAG:Gambiaanse   GH:Ghanese   GN:Guinese   CI:Ivoriaanse   CV:Kaapverdische   TC:Kameroense   EAK:Kenyaanse   CD:ZaÃ¯rese   LS:Lesothaanse   LB:Liberiaanse   LAR:Libische   RM:Malagassische   MW:Malawische   RMM:Malinese   MA:Marokkaanse   RIM:Burger van Mauritanië   MS:Burger van Mauritius   MOC:Mozambiquaanse   SD:Swazische   RN:Burger van Niger   WAN:Burger van Nigeria   EAU:Ugandese   GW:Guineebissause   ZA:Zuidafrikaanse   ZW:Zimbabwaanse   RWA:Rwandese   ST:Burger van SÃ£o TomÃ© en Principe   SN:Senegalese   WAL:Sierraleoonse   SUD:Soedanese   SP:Somalische   EAT:Tanzaniaanse   TG:Togolese   TS:Tsjadische   TN:Tunesische   Z:Zambiaanse   ZSUD:Zuid-Soedanese   BS:Bahamaanse   BH:Belizaanse   CDN:Canadese   CR:Costaricaanse   C:Cubaanse   DOM:Burger van Dominicaanse Republiek   EL:Salvadoraanse   GCA:Guatemalteekse   RH:HaÃ¯tiaanse   HON:Hondurese   JA:Jamaicaanse   MEX:Mexicaanse   NIC:Nicaraguaanse   PA:Panamese   TT:Burger van Trinidad en Tobago   USA:Amerikaans burger   RA:Argentijnse   BDS:Barbadaanse   BOL:Boliviaanse   BR:Braziliaanse   RCH:Chileense   CO:Colombiaanse   EC:Ecuadoraanse   GUY:Guyaanse   PY:Paraguayaanse   PE:Peruaanse   SME:Surinaamse   ROU:Uruguayaanse   YV:Venezolaanse   WG:Grenadaanse   KN:Burger van Saint Kitts-Nevis   SK:Slowaakse   CZ:Tsjechische   BA:Burger van Bosnië-Herzegovina   GE:Burger van Georgië   AFG:Afgaanse   BRN:Bahreinse   BT:Bhutaanse   BM:Burmaanse   BRU:Bruneise   K:Kambodjaanse   CL:Srilankaanse   CN:Chinese   CY:Cyprische   RP:Filipijnse   TMN:Burger van Toerkmenistan   RC:Taiwanese   IND:Burger van India   RI:Indonesische   IRQ:Iraakse   IR:Iraanse   IL:Israëlische   J:Japanse   HKJ:Jordaanse   TAD:Burger van Tadzjikistan   KWT:Koeweitse   LAO:Laotiaanse   RL:Libanese   MV:Maldivische   MAL:Maleisische   MON:Mongolische   OMA:Omanitische   NPL:Nepalese   KO:Noordkoreaanse   OEZ:Burger van Oezbekistan   PK:Pakistaanse   KG:Katarese   AS:Saoediarabische   SGP:Singaporaanse   SYR:Syrische   T:Thaise   AE:Burger van de Ver. Arabische Emiraten   TR:Turkse   UA:Burger van Oekraine   ROK:Zuidkoreaanse   VN:Viëtnamese   BD:Burger van Bangladesh   KYR:Burger van Kyrgyzstan   MD:Burger van Moldavië   KZ:Burger van Kazachstan   BY:Burger van Belarus (Wit-Rusland)   AZ:Burger van Azerbajdsjan   AM:Burger van Armenië   AUS:Australische   PNG:Burger van Papua-Nieuwguinea   NZ:Nieuwzeelandse   WSM:Westsamoaanse   RUS:Burger van Rusland   SLO:Burger van Slovenië   AG:Burger van Antigua en Barbuda   VU:Vanuatuse   FJI:Fijische   GB4:Burger van Britse afhankelijke gebieden   HR:Burger van Kroatië   TO:Tongaanse   NR:Nauruaanse   USA2:Amerikaans onderdaan   LV:Letse   SB:Solomoneilandse   SY:Seychelse   KIR:Kiribatische   TV:Tuvaluaanse   WL:Sintluciaanse   WD:Burger van Dominica   WV:Burger van Sint Vincent en de Grenadinen   EW:Estnische   IOT:British National (overseas)   ZRE:ZaÃ¯rese (Congolese)   TLS:Burger van Timor Leste   SCG:Burger van Servië en Montenegro   SRB:Burger van Servië   MNE:Burger van Montenegro   LT:Litouwse   MAR:Burger van de Marshalleilanden   BUR:Myanmarese   SWA:Namibische   499:Staatloos   AL:Albanese   AND:Andorrese   B:Belgische   BG:Bulgaarse   DK:Deense   D:Duitse   FIN:Finse   F:Franse   YMN:Jemenitische   GR:Griekse   GB:Brits burger   H:Hongaarse   IRL:Ierse   IS:IJslandse   I:Italiaanse   YU:Joegoslavische   FL:Liechtensteinse   L:Luxemburgse   M:Maltese   MC:Monegaskische   N:Noorse   A:Oostenrijkse   PL:Poolse   P:Portugese   RO:Roemeense   RSM:Sanmarinese   E:Spaanse   VAT:Vaticaanse   S:Zweedse   CH:Zwitserse   GB2:Brits onderdaan   ERI:Eritrese   GB3:Brits overzees burger   MK:Macedonische   XK:Kosovaar
                        //
                        'PsNa' => [],
                        // Geboortedatum
                        'DaBi' => [],
                        // Geboorteland (verwijzing naar: Land => AfasKnCountry)
                        'CoBi' => [],
                        // Geboorteplaats (verwijzing naar: Woonplaats => AfasKnResidence)
                        'RsBi' => [],
                        // BSN
                        'SoSe' => [
                            'alias' => 'bsn',
                        ],
                        // Burgerlijke staat (verwijzing naar: Tabelwaarde,Burgerlijke staat => AfasKnCodeTableValue)
                        'ViCs' => [],
                        // Huwelijksdatum
                        'DaMa' => [],
                        // Datum scheiding
                        'DaDi' => [],
                        // Overlijdensdatum
                        'DaDe' => [],
                        // Titel/aanhef (verwijzing naar: Titel => AfasKnTitle)
                        'TtId' => [
                            // ALG was given in Qoony (where person was inside knSalesRelationPer).
                            // in newer environment where it's inside knOrganisation > knContact,
                            // I don't even see this one in an entry screen.
                            //'default' => 'ALG',
                        ],
                        // Tweede titel (verwijzing naar: Titel => AfasKnTitle)
                        'TtEx' => [],
                        // Briefaanhef
                        'LeHe' => [],
                        // Telefoonnr. werk
                        'TeNr' => [
                            // Note aliases change for KnSalesRelationPer, see below.
                            'alias' => 'phone',
                        ],
                        // Telefoonnr. privé
                        'TeN2' => [],
                        // Fax werk
                        'FaNr' => [
                            'alias' => 'fax',
                        ],
                        // Mobiel werk
                        'MbNr' => [
                            'alias' => 'mobile',
                        ],
                        // Mobiel privé
                        'MbN2' => [],
                        // E-mail werk
                        'EmAd' => [
                            'alias' => 'email',
                        ],
                        'EmA2' => [],
                        // Homepage
                        'HoPa' => [
                            'alias' => 'homepage',
                        ],
                        // Correspondentie
                        'Corr' => [
                            'type' => 'boolean',
                            'default' => false,
                        ],
                        // Voorkeursmedium (verwijzing naar: Tabelwaarde,Medium voor correspondentie => AfasKnCodeTableValue)
                        'ViMd' => [],
                        // Opmerking
                        'Re' => [
                            'alias' => 'comment',
                        ],
                        // Status (verwijzing naar: Tabelwaarde,Status verkooprelatie => AfasKnCodeTableValue)
                        'StId' => [],
                        // Sociale netwerken
                        'SocN' => [],
                        // Facebook
                        'Face' => [
                            'alias' => 'facebook',
                        ],
                        // LinkedIn
                        'Link' => [
                            'alias' => 'linkedin',
                        ],
                        // Twitter
                        'Twtr' => [
                            'alias' => 'twitter',
                        ],
                        // Naam bestand
                        'FileName' => [],
                        // Afbeelding (base64Binary field)
                        'FileStream' => [],
                        // Persoon toegang geven tot afgeschermde deel van de portal(s)
                        'AddToPortal' => [
                            'type' => 'boolean',
                        ],
                        // E-mail toegang
                        'EmailPortal' => [],
                    ],
                ];

                // First name is not required if initials are filled.
                if (!empty($data['In']) || !empty($data['initials'])) {
                    unset($info['fields']['FiNm']['required']);
                }

                // We're sure that the record will be newly inserted if MatchPer
                // specifies this, and the 'fields action' does not say otherwise.
                // @todo test: what about MatchPer 7 and "update"? What overrides what?
                $inserting = (!$fields_action || $fields_action === 'insert')
                    && (!isset($data['match_method']) && !isset($data['MatchPer'])
                        || isset($data['match_method']) && $data['match_method'] == 7
                        || isset($data['MatchPer']) && $data['MatchPer'] == 7);

                // MatchPer defaults: Our principle is we would rather insert duplicate
                // data than silently overwrite data by accident.
                if (!empty($data['BcCo'])) {
                    // ...but it seems very unlikely that someone would specify BcCo when
                    // they don't explicitly want the corresponding record overwritten.
                    // So we match on BcCo in that case. This means there is no difference
                    // between $fields_action "insert" and "update"!
                    // If we do _not_ set MatchPer while BcCo _is_ specified, with
                    // $fields_action "insert" we get error "Unsupported match value!!"
                    $info['fields']['MatchPer']['default!'] = '0';
                } elseif (!empty($data['SoSe']) || !empty($data['bsn'])) {
                    // I guess we can assume the same logic (we never want duplicate
                    // records so just update everything silently, even for inserts), for
                    // BSN...
                    $info['fields']['MatchPer']['default!'] = '1';
                }
                // TODO we can surely assume the same logic for 2-6 but this would take
                // a lot of testing. Do this later. We will feel entitled to change this
                // part of this function's behavior silently.
                else {
                    // Probably even with $fields_action "update", a new record will be
                    // inserted if there is no match... but we do not know this for sure!
                    // Since our principle is to prevent silent overwrites of data, we
                    // here force an error for "update" if MatchPer is not explicitly
                    // specified in $data.
                    // (If you disagree / encounter circumstances where this is not OK,
                    // tell me so we can refine this. --Roderik.)
                    //
                    // If we set MatchPer=0 if BcCo is not specified,
                    // - we get error "Voer een waarde in bij 'Nummer'" at "update";
                    // - a record is always inserted at "insert" (so I guess in this case
                    //   '0' is equal to '7').
                    // (NOTE we haven't actually tested this until now, just assumed that
                    // it works the same way as for knOrganisation, which was tested...)
                    $info['fields']['MatchPer']['default!'] = '0';
                }

                if ($parent_type === 'KnContact' || $parent_type === 'KnSalesRelationPer') {
                    // Note: a knPerson cannot be inside a knContact directly. So far we
                    // know only of the situation where that knContact is again inside a
                    // knOrganisation.

                    $info['fields'] += [
                        // This field applies to a knPerson inside a knContact inside a
                        // knOrganisation:
                        // Land wetgeving (verwijzing naar: Land => AfasKnCountry)
                        'CoLw' => [],
                    ];
                }
                if ($parent_type === 'KnSalesRelationPer') {
                    // Usually, phone/mobile/e-mail aliases are set to the business
                    // ones, and these are the ones you see on the screen in the UI.
                    // Inside KnSalesRelationPer, you see the private equivalents in the
                    // UI. (At least that was the case for Qoony.) So it's those you want
                    // to fill by default.
                    $info['fields']['TeN2']['alias'] = $info['fields']['TeNr']['alias'];
                    unset($info['fields']['TeNr']['alias']);
                    $info['fields']['MbN2']['alias'] = $info['fields']['MbNr']['alias'];
                    unset($info['fields']['MbNr']['alias']);
                    $info['fields']['EmA2']['alias'] = $info['fields']['EmAd']['alias'];
                    unset($info['fields']['EmAd']['alias']);
                }
                break;

            case 'KnSalesRelationPer':
                // NOTE - not checked against XSD yet, only taken over from Qoony example
                // Fields:
                // ??? = Overheids Identificatienummer, which an AFAS expert recommended
                //       for using as a secondary-unique-id, when we want to insert an
                //       auto-numbered item and later retrieve it to get the inserted ID.
                //       I don't know what this is but it's _not_ 'OIN', I tried that.
                //       (In the end we never used this field.)
                $info = [
                    'id_field' => 'DbId',
                    'objects' => [
                        'KnPerson' => 'person',
                    ],
                    'fields' => [

                        // 'is debtor'?
                        'IsDb' => [
                            'type' => 'boolean',
                            'default' => true,
                        ],
                        // According to AFAS docs, PaCd / VaDu "are required if IsDb==True" ...
                        // no further specs. Heh, VaDu is not even in our inserted XML.
                        'PaCd' => [
                            'default' => '14',
                        ],
                        'CuId' => [
                            'alias' => 'currency_code',
                            'default' => 'EUR',
                        ],
                        'Bl' => [
                            'default' => 'false',
                        ],
                        'AuPa' => [
                            'default' => '0',
                        ],
                        // Verzamelrekening Debiteur -- apparently these just need to be
                        // specified by whoever is setting up the AFAS administration?
                        'ColA' => [
                            'alias' => 'verzamelreking_debiteur',
                        ],
                        // ?? Doesn't seem to be required, but we're still setting default to
                        // the old value we're used to, until we know what this field means.
                        'VtIn' => [
                            'default' => '1',
                        ],
                        'PfId' => [
                            'default' => '*****',
                        ],
                    ],
                ];
                break;

            case 'KnOrganisation':
                $info = [
                    'objects' => [
//            'KnBankAccount' => 'bank_account',
                        'KnBasicAddressAdr' => 'address',
                        'KnBasicAddressPad' => 'postal_address',
                        'KnContact' => 'contact',
                    ],
                    'fields' => [
                        // Postadres is adres
                        'PbAd' => [
                            'alias' => 'is_po_box',
                            'type' => 'boolean',
                            'default' => false,
                        ],
                        'AutoNum' => [
                            'alias' => 'auto_num',
                            'type' => 'boolean',
                        ],
                        /**
                         * If you specify MatchOga and if the corresponding fields have
                         * values, the difference between $fields_action "update" and
                         * "insert" falls away: if there is a match (but only one) the
                         * existing record is updated. If there isn't, a new one is
                         * inserted. If there are multiple matches, or a wrong match method
                         * is specified, AFAS throws an error.
                         *
                         * We make sure that you must explicitly specify a value for this
                         * with $field_action "update" (and get an error if you don't), by
                         * setting the default - see further down.
                         */
                        // Organisatie vergelijken op
                        // Values:  0:Zoek op BcCo   1:KvK-nummer   2:Fiscaal nummer   3:Naam   4:Adres   5:Postadres   6:Altijd nieuw toevoegen
                        'MatchOga' => [
                            'alias' => 'match_method',
                        ],
                        // Organisatie/persoon (intern)
                        // From "Organisaties toevoegen en wijzigen (UpdateConnector KnOrganisation)":
                        // "Do not deliver the 'BcId' field."
                        // (Because it really is internal. So why should we define it?)
                        //'BcId' => [
                        //),
                        // Nummer, 1-15 chars
                        'BcCo' => [
                            // 'ID' would be more confusing because it's not the internal ID.
                            'alias' => 'number',
                        ],
                        'SeNm' => [
                            'alias' => 'search_name',
                        ],
                        // Name. Is not required officially, but I guess you must fill in either
                        // BcCo, SeNm or Nm to be able to find the record back. (Or maybe you get an
                        // error if you don't specify any.)
                        'Nm' => [
                            'alias' => 'name',
                        ],
                        // Rechtsvorm (verwijzing naar: Tabelwaarde,Rechtsvorm => AfasKnCodeTableValue)
                        'ViLe' => [
                            'alias' => 'org_type',
                        ],
                        // Branche (verwijzing naar: Tabelwaarde,Branche => AfasKnCodeTableValue)
                        'ViLb' => [
                            'alias' => 'branche',
                        ],
                        // KvK-nummer
                        'CcNr' => [
                            'alias' => 'coc_number',
                        ],
                        // Datum KvK
                        'CcDa' => [
                            'type' => 'date',
                        ],
                        // Naam (statutair)
                        'NmRg' => [],
                        // Vestiging (statutair)
                        'RsRg' => [],
                        // Titel/aanhef (verwijzing naar: Titel => AfasKnTitle)
                        'TtId' => [],
                        // Briefaanhef
                        'LeHe' => [],
                        // Organisatorische eenheid (verwijzing naar: Organisatorische eenheid => AfasKnOrgUnit)
                        'OuId' => [],
                        // Telefoonnr. werk
                        'TeNr' => [
                            'alias' => 'phone',
                        ],
                        // Fax werk
                        'FaNr' => [
                            'alias' => 'fax',
                        ],
                        // Mobiel werk
                        'MbNr' => [
                            'alias' => 'mobile',
                        ],
                        // E-mail werk
                        'EmAd' => [
                            'alias' => 'email',
                        ],
                        // Homepage
                        'HoPa' => [
                            'alias' => 'homepage',
                        ],
                        // Correspondentie
                        'Corr' => [
                            'type' => 'boolean',
                        ],
                        // Voorkeursmedium (verwijzing naar: Tabelwaarde,Medium voor correspondentie => AfasKnCodeTableValue)
                        'ViMd' => [],
                        // Opmerking
                        'Re' => [
                            'alias' => 'comment',
                        ],
                        // Fiscaalnummer
                        'FiNr' => [
                            'alias' => 'fiscal_number',
                        ],
                        // Status (verwijzing naar: Tabelwaarde,Status verkooprelatie => AfasKnCodeTableValue)
                        'StId' => [],
                        // Sociale netwerken
                        'SocN' => [],
                        // Facebook
                        'Face' => [
                            'alias' => 'facebook',
                        ],
                        // LinkedIn
                        'Link' => [
                            'alias' => 'linkedin',
                        ],
                        // Twitter
                        'Twtr' => [
                            'alias' => 'twitter',
                        ],
                        // Onderdeel van organisatie (verwijzing naar: Organisatie/persoon => AfasKnBasicContact)
                        'BcPa' => [],
                    ],
                ];

                // We're sure that the record will be newly inserted if MatchPer
                // specifies this, and the 'fields action' does not say otherwise.
                // @todo test: what about MatchOga 6 and "update"? What overrides what?
                $inserting = (!$fields_action || $fields_action === 'insert')
                    && (!isset($data['match_method']) && !isset($data['MatchOga'])
                        || isset($data['match_method']) && $data['match_method'] == 6
                        || isset($data['MatchOga']) && $data['MatchOga'] == 6);

                // MatchOga defaults: Our principle is we would rather insert duplicate
                // data than silently overwrite data by accident.
                if (!empty($data['BcCo'])) {
                    // ...but it seems very unlikely that someone would specify BcCo when
                    // they don't explicitly want the corresponding record overwritten.
                    // So we match on BcCo in that case. This means there is no difference
                    // between $fields_action "insert" and "update"!
                    // If we do _not_ set MatchOga while BcCo _is_ specified, with
                    // $fields_action "insert" we get error "Unsupported match value!!"
                    $info['fields']['MatchOga']['default!'] = '0';
                } elseif (!empty($data['CcNr']) || !empty($data['coc_number'])) {
                    // I guess we can assume the same logic (we never want duplicate
                    // records so just update everything silently, even for inserts), for
                    // KvK number...
                    $info['fields']['MatchOga']['default!'] = '1';
                } elseif (!empty($data['FiNr']) || !empty($data['fiscal_number'])) {
                    // ...and fiscal number.
                    $info['fields']['MatchOga']['default!'] = '2';
                } elseif ($fields_action === 'insert') {
                    // Since we can get an error if not setting MatchOga in some
                    // circumstances (see 0 above), explicitly set 'always insert'.
                    $info['fields']['MatchOga']['default!'] = '6';
                } else {
                    // Probably even with $fields_action "update", a new record will be
                    // inserted if there is no match... but we do not know this for sure!
                    // Since our principle is to prevent silent overwrites of data, we
                    // here force an error for "update" if MatchOga is not explicitly
                    // specified in $data.
                    // (If you disagree / encounter circumstances where this is not OK,
                    // tell me so we can refine this. --Roderik.)
                    //
                    // If we set MatchOga=0 if BcCo is not specified,
                    // - we get error "Voer een waarde in bij 'Nummer'" at "update";
                    // - a record is always inserted at "insert" (so I guess in this case
                    //   '0' is equal to '6').
                    $info['fields']['MatchOga']['default!'] = '0';
                }
                break;

            case 'KnSubject':
                $info = [
                    'id_field' => 'SbId',
                    'objects' => [
                        'KnSubjectLink' => 'subject_link',
                        'KnS01' => 'subject_link_1',
                        'KnS02' => 'subject_link_2',
                        // If there are more KnSNN, they have all custom fields?
                    ],
                    'fields' => [
                        // Type dossieritem (verwijzing naar: Type dossieritem => AfasKnSubjectType)
                        'StId' => [
                            'alias' => 'type',
                            'type' => 'long',
                            'required' => true,
                        ],
                        // Onderwerp
                        'Ds' => [
                            'alias' => 'description',
                        ],
                        // Toelichting
                        'SbTx' => [
                            'alias' => 'comment',
                        ],
                        // Instuurdatum
                        'Da' => [
                            'alias' => 'date',
                            'type' => 'date',
                        ],
                        // Verantwoordelijke (verwijzing naar: Medewerker => AfasKnEmployee)
                        'EmId' => [
                            'alias' => 'responsible',
                        ],
                        // Aanleiding (verwijzing naar: Dossieritem => AfasKnSubject)
                        'SbHi' => [
                            'type' => 'long',
                        ],
                        // Type actie (verwijzing naar: Type actie => AfasKnSubjectActionType)
                        'SaId' => [
                            'alias' => 'action_type',
                        ],
                        // Prioriteit (verwijzing naar: Tabelwaarde,Prioriteit actie => AfasKnCodeTableValue)
                        'ViPr' => [],
                        // Bron (verwijzing naar: Brongegevens => AfasKnSourceData)
                        'ScId' => [
                            'alias' => 'source',
                        ],
                        // Begindatum
                        'DtFr' => [
                            'alias' => 'start_date',
                            'type' => 'date',
                        ],
                        // Einddatum
                        'DtTo' => [
                            'alias' => 'end_date',
                            'type' => 'date',
                        ],
                        // Afgehandeld
                        'St' => [
                            'alias' => 'done',
                            'type' => 'boolean',
                        ],
                        // Datum afgehandeld
                        'DtSt' => [
                            'alias' => 'done_date',
                            'type' => 'date',
                        ],
                        // Waarde kenmerk 1 (verwijzing naar: Waarde kenmerk => AfasKnFeatureValue)
                        'FvF1' => [
                            'type' => 'long',
                        ],
                        // Waarde kenmerk 2 (verwijzing naar: Waarde kenmerk => AfasKnFeatureValue)
                        'FvF2' => [
                            'type' => 'long',
                        ],
                        // Waarde kenmerk 3 (verwijzing naar: Waarde kenmerk => AfasKnFeatureValue)
                        'FvF3' => [
                            'type' => 'long',
                        ],
                        // Geblokkeerd
                        'SbBl' => [
                            'alias' => 'blocked',
                            'type' => 'boolean',
                        ],
                        // Bijlage
                        'SbPa' => [
                            'alias' => 'attachment',
                        ],
                        // Save file with subject
                        'FileTrans' => [
                            'type' => 'boolean',
                        ],
                        // File as byte-array
                        'FileStream' => [],
                    ],
                ];
                break;

            case 'KnSubjectLink':
                $info = [
                    'id_field' => 'SbId',
                    'fields' => [
                        // Save in CRM Subject
                        'DoCRM' => [
                            'type' => 'boolean',
                        ],
                        // Organisatie/persoon
                        'ToBC' => [
                            'alias' => 'is_org_person',
                            'type' => 'boolean',
                        ],
                        // Medewerker
                        'ToEm' => [
                            'alias' => 'is_employee',
                            'type' => 'boolean',
                        ],
                        // Verkooprelatie
                        'ToSR' => [
                            'alias' => 'is_sales_relation',
                            'type' => 'boolean',
                        ],
                        // Inkooprelatie
                        'ToPR' => [
                            'alias' => 'is_purchase_relation',
                            'type' => 'boolean',
                        ],
                        // Cliënt IB
                        'ToCl' => [
                            'alias' => 'is_client_ib',
                            'type' => 'boolean',
                        ],
                        // Cliënt Vpb
                        'ToCV' => [
                            'alias' => 'is_client_vpb',
                            'type' => 'boolean',
                        ],
                        // Werkgever
                        'ToEr' => [
                            'alias' => 'is_employer',
                            'type' => 'boolean',
                        ],
                        // Sollicitant
                        'ToAp' => [
                            'alias' => 'is_applicant',
                            'type' => 'boolean',
                        ],
                        // Type bestemming
                        // Values:  1:Geen   2:Medewerker   3:Organisatie/persoon   4:Verkooprelatie   8:Cliënt IB   9:Cliënt Vpb   10:Werkgever   11:Inkooprelatie   17:Sollicitant   30:Campagne   31:Item   32:Cursusevenement-->
                        'SfTp' => [
                            'alias' => 'destination_type',
                            'type' => 'long',
                        ],
                        // Bestemming
                        'SfId' => [
                            'alias' => 'destination_id',
                        ],
                        // Organisatie/persoon (verwijzing naar: Organisatie/persoon => AfasKnBasicContact)
                        'BcId' => [
                            'alias' => 'org_person',
                        ],
                        // Contact (verwijzing naar: Contact => AfasKnContactData)
                        'CdId' => [
                            'alias' => 'contact',
                            'type' => 'long',
                        ],
                        // Administratie (Verkoop) (verwijzing naar: Administratie => AfasKnUnit)
                        'SiUn' => [
                            'type' => 'long',
                        ],
                        // Factuurtype (verkoop) (verwijzing naar: Type factuur => AfasFiInvoiceType)
                        'SiTp' => [
                            'alias' => 'sales_invoice_type',
                            'type' => 'long',
                        ],
                        // Verkoopfactuur (verwijzing naar: Factuur => AfasFiInvoice)
                        'SiId' => [
                            'alias' => 'sales_invoice',
                        ],
                        // Administratie (Inkoop) (verwijzing naar: Administratie => AfasKnUnit)
                        'PiUn' => [
                            'type' => 'long',
                        ],
                        // Factuurtype (inkoop) (verwijzing naar: Type factuur => AfasFiInvoiceType)
                        'PiTp' => [
                            'alias' => 'purchase_invoice_type',
                            'type' => 'long',
                        ],
                        // Inkoopfactuur (verwijzing naar: Factuur => AfasFiInvoice)
                        'PiId' => [
                            'alias' => 'purchase_invoice',
                        ],
                        // Fiscaal jaar (verwijzing naar: Aangiftejaren => AfasTxDeclarationYear)
                        'FiYe' => [
                            'alias' => 'fiscal_year',
                            'type' => 'long',
                        ],
                        // Project (verwijzing naar: Project => AfasPtProject)
                        'PjId' => [
                            'alias' => 'project',
                        ],
                        // Campagne (verwijzing naar: Campagne => AfasCmCampaign)
                        'CaId' => [
                            'alias' => 'campaign',
                            'type' => 'long',
                        ],
                        // Actief (verwijzing naar: Vaste activa => AfasFaFixedAssets)
                        'FaSn' => [
                            'type' => 'long',
                        ],
                        // Voorcalculatie (verwijzing naar: Voorcalculatie => AfasKnQuotation)
                        'QuId' => [],
                        // Dossieritem (verwijzing naar: Dossieritem => AfasKnSubject)
                        'SjId' => [
                            'type' => 'long',
                        ],
                        // Abonnement (verwijzing naar: Abonnement => AfasFbSubscription
                        'SuNr' => [
                            'alias' => 'subscription',
                            'type' => 'long',
                        ],
                        // Dienstverband
                        'DvSn' => [
                            'type' => 'long',
                        ],
                        // Type item (verwijzing naar: Tabelwaarde,Itemtype => AfasKnCodeTableValue)
                        // Values:  Wst:Werksoort   Pid:Productie-indicator   Deg:Deeg   Dim:Artikeldimensietotaal   Art:Artikel   Txt:Tekst   Sub:Subtotaal   Tsl:Toeslag   Kst:Kosten   Sam:Samenstelling   Crs:Cursus-->
                        'VaIt' => [
                            'alias' => 'item_type',
                        ],
                        // Itemcode (verwijzing naar: Item => AfasFbBasicItems)
                        'BiId' => [
                            'alias' => 'item_code',
                        ],
                        // Cursusevenement (verwijzing naar: Evenement => AfasKnCourseEvent)
                        'CrId' => [
                            'alias' => 'course_event',
                            'type' => 'long',
                        ],
                        // Verzuimmelding (verwijzing naar: Verzuimmelding => AfasHrAbsIllnessMut)
                        'AbId' => [
                            'type' => 'long',
                        ],
                        // Forecast (verwijzing naar: Forecast => AfasCmForecast)
                        'FoSn' => [
                            'type' => 'long',
                        ],
                    ],
                ];
                break;

            // Subject link #1 (after KnSubjectLink), to be sent inside KnSubject.
            // The field names are not custom fields, but are the definitions general?
            // Not 100% sure.
            case 'KnS01':
                $info = [
                    'id_field' => 'SbId',
                    'fields' => [
                        // Vervaldatum
                        'U001' => [
                            'alias' => 'end_date',
                            'type' => 'date',
                        ],
                        // Identiteitsnummer
                        'U002' => [
                            'alias' => 'id_number',
                        ],
                    ],
                ];
                break;

            case 'KnS02':
                $info = [
                    'id_field' => 'SbId',
                    'fields' => [
                        // Contractnummer
                        'U001' => [
                            'alias' => 'contract_number',
                        ],
                        // Begindatum contract
                        'U002' => [
                            'alias' => 'start_date',
                            'type' => 'date',
                        ],
                        // Einddatum contract
                        'U003' => [
                            'alias' => 'start_date',
                            'type' => 'date',
                        ],
                        // Waarde
                        'U004' => [
                            'alias' => 'value',
                            'type' => 'decimal',
                        ],
                        // Beëindigd
                        'U005' => [
                            'alias' => 'ended',
                            'type' => 'boolean',
                        ],
                        // Stilzwijgend verlengen
                        'U006' => [
                            'alias' => 'recurring',
                            'type' => 'boolean',
                        ],
                        // Opzegtermijn (verwijzing naar: Tabelwaarde,(Afwijkende) opzegtermijn => AfasKnCodeTableValue)
                        'U007' => [
                            'alias' => 'cancel_term',
                        ],
                    ],
                ];
                break;

            case 'FbSales':
                $info = [
                    'objects' => [
                        'FbSalesLines' => 'line_item',
                    ],
                    'fields' => [
                        // Nummer
                        'OrNu' => [],
                        // Datum
                        'OrDa' => [
                            'alias' => 'date',
                            'type' => 'date',
                        ],
                        // Verkooprelatie (verwijzing naar: Verkooprelatie => AfasKnSalRelation)
                        'DbId' => [
                            'alias' => 'sales_relation',
                        ],
                        // Gewenste leverdatum
                        'DaDe' => [
                            'alias' => 'delivery_date_req',
                            'type' => 'date',
                        ],
                        // Datum levering (toegezegd)
                        'DaPr' => [
                            'alias' => 'delivery_date_ack',
                            'type' => 'date',
                        ],
                        // Valutacode (verwijzing naar: Valuta => AfasKnCurrency)
                        'CuId' => [
                            'alias' => 'currency_code',
                        ],
                        // Valutakoers
                        'Rate' => [
                            'alias' => 'currency_rate',
                        ],
                        // Backorder
                        'BkOr' => [
                            'type' => 'boolean',
                        ],
                        // Verkoopkanaal (verwijzing naar: Tabelwaarde,Verkoopkanaal => AfasKnCodeTableValue)
                        'SaCh' => [
                            'alias' => 'sales_channel',
                        ],
                        // Btw-plicht (verwijzing naar: Btw-plicht => AfasKnVatDuty)
                        'VaDu' => [
                            'alias' => 'vat_due',
                        ],
                        // Prijs incl. btw
                        'InVa' => [
                            'alias' => 'includes_vat',
                        ],
                        // Betalingsvoorwaarde (verwijzing naar: Betalingsvoorwaarde => AfasKnPaymentCondition)
                        'PaCd' => [],
                        // Betaalwijze (verwijzing naar: Betaalwijze => AfasKnPaymentType)
                        'PaTp' => [
                            'alias' => 'payment_type',
                        ],
                        // Opmerking
                        'Re' => [
                            'alias' => 'comment',
                        ],
                        // Administratie (verwijzing naar: Administratieparameters Algemeen => AfasKnUnitPar)
                        'Unit' => [
                            'type' => 'long',
                        ],
                        // Incasseren
                        'Coll' => [
                            'type' => 'boolean',
                        ],
                        // Creditorder
                        'CrOr' => [
                            'type' => 'boolean',
                        ],
                        // Code route (verwijzing naar: Tabelwaarde,Routes => AfasKnCodeTableValue)
                        'Rout' => [],
                        // Magazijn (verwijzing naar: Magazijn => AfasFbWarehouse)
                        'War' => [
                            'alias' => 'warehouse',
                        ],
                        // Verzamelpakbon
                        'CoDn' => [
                            'type' => 'boolean',
                        ],
                        // Verzamelfactuur
                        'CoIn' => [
                            'type' => 'boolean',
                        ],
                        // Prioriteit levering
                        'DlPr' => [
                            'alias' => 'delivery_prio',
                            'type' => 'long',
                        ],
                        // Taal (verwijzing naar: Talen => AfasKnLanguage)
                        'LgId' => [
                            'alias' => 'language',
                        ],
                        // Leveringsconditie (verwijzing naar: Tabelwaarde,Leveringvoorwaarde => AfasKnCodeTableValue)
                        // Values:  0:Deellevering toestaan   1:Regel volledig uitleveren   2:Order volledig uitleveren   3:Geen backorders leveren
                        'DeCo' => [
                            'alias' => 'delivery_cond',
                        ],
                        // CBS-typen (verwijzing naar: CBS-typen => AfasFbCBSType)
                        'CsTy' => [
                            'alias' => 'cbs_type',
                        ],
                        // Type vervoer CBS (verwijzing naar: Tabelwaarde,CBS Vervoerswijze => AfasKnCodeTableValue)
                        // Values:  1:Zeevaart   2:Spoorvervoer   3:Wegvervoer   4:Luchtvaart   5:Postzendingen   7:Pijpleidingvervoer   8:Binnenvaart   9:Eigen vervoer
                        'VaTr' => [],
                        // Statistisch stelsel CBS (verwijzing naar: Tabelwaarde,CBS Statistisch stelsel => AfasKnCodeTableValue)
                        // Values:  00:Reguliere invoer/ICV en uitvoer/ICL   01:Doorlevering (ICL) van onbewerkte goederen naar een andere Eu-lidstaat   02:Wederverkoop (ICL of uitvoer) van onbewerkte goederen   03:Invoer (al of niet via douane-entrepot) van goederen   04:Verwerving/levering vÃ³Ã³r eigen voorraadverplaatsing (fictieve zending)   05:Verwerving/levering nÃ¡ eigen voorraadverplaatsing (fictieve zending)   10:Actieve douaneveredeling met toepassing van het terugbetalingssysteem
                        'VaSt' => [],
                        // Goederenstroom CBS (verwijzing naar: Tabelwaarde,CBS Goederenstroom => AfasKnCodeTableValue)
                        // 6:Invoer/intra-cummunautaire verwerving (ICV)   7:Uitvoer/intra-communautaire levering (ICL)
                        'VaGs' => [],
                        // Transactie CBS (verwijzing naar: Tabelwaarde,CBS Transactie => AfasKnCodeTableValue)
                        // Values:  1:Koop, verkoop of huurkoop (financiële leasing)   2:Retourzending (excl. retour tijdelijke in- en uitvoer, zie code 6)   3:Gratis zending   4:Ontvangst of verzending vÃ³Ã³r loonveredeling   5:Ontvangst of verzending nÃ¡ loonveredeling   6:Tijdelijke in- en uitvoer en retour tijdelijke in- en uitvoer   7:Ontvangst of verzending in het kader van gecoÃ¶rdineerde fabrikage   8:Levering i.v.m. bouwmaterialen c.q. bouwkunde onder algemeen contract
                        'VaTa' => [],
                        // Land bestemming CBS (verwijzing naar: Land => AfasKnCountry)
                        'CoId' => [],
                        // Factuurkorting (%)
                        'InPc' => [
                            'type' => 'decimal',
                        ],
                        // Kredietbeperking inclusief btw
                        'VaCl' => [
                            'type' => 'boolean',
                        ],
                        // Kredietbeperking (%)
                        'ClPc' => [
                            'type' => 'decimal',
                        ],
                        // Betalingskorting (%)
                        'PaPc' => [
                            'type' => 'decimal',
                        ],
                        // Betalingskorting incl. btw
                        'VaPa' => [
                            'type' => 'boolean',
                        ],
                        // Afwijkende btw-tariefgroep
                        'VaYN' => [
                            'type' => 'boolean',
                        ],
                        // Type barcode (verwijzing naar: Tabelwaarde,Type barcode => AfasKnCodeTableValue)-->
                        // Values:  0:Geen controle   1:Barcode EAN8   2:Barcode UPC   3:Barcode EAN13   4:Barcode EAN14   5:Barcode SSCC   6:Code 128   7:Interleaved 2/5   8:Interleaved 2/5 (controlegetal)
                        'VaBc' => [
                            'alias' => 'barcode_type',
                        ],
                        // Barcode
                        'BaCo' => [
                            'alias' => 'barcode',
                        ],
                        // Rapport (verwijzing naar: Definitie => AfasKnMetaDefinition)
                        'PrLa' => [],
                        // Dagboek factuur (verwijzing naar: Dagboek => AfasKnJournal)
                        'JoCo' => [
                            'alias' => 'journal',
                        ],
                        // Factureren aan (verwijzing naar: Verkooprelatie => AfasKnSalRelation)
                        'FaTo' => [
                            'alias' => 'invoice_to',
                        ],
                        // Toekomstige order
                        'FuOr' => [
                            'alias' => 'future_order',
                            'type' => 'boolean',
                        ],
                        // Type levering (verwijzing naar: Type levering => AfasFbDeliveryType)
                        'DtId' => [
                            'alias' => 'delivery_type',
                            'type' => 'long',
                        ],
                        // Project (verwijzing naar: Project => AfasPtProject)
                        'PrId' => [
                            'alias' => 'project',
                        ],
                        // Projectfase (verwijzing naar: Projectfase => AfasPtProjectStage)
                        'PrSt' => [
                            'alias' => 'project_stage',
                        ],
                        // Status verzending (verwijzing naar: Tabelwaarde,Verzendstatus => AfasKnCodeTableValue)
                        // Values:  0:Niet aanbieden aan vervoerder   1:Aanbieden aan vervoerder   2:Aangeboden aan vervoerder   3:Verzending correct ontvangen   4:Fout bij aanbieden verzending
                        'SeSt' => [
                            'alias' => 'delivery_state',
                        ],
                        // Verzendgewicht
                        'SeWe' => [
                            'alias' => 'weight',
                            'type' => 'decimal',
                        ],
                        // Aantal colli
                        'QuCl' => [
                            'type' => 'long',
                        ],
                        // Verpakking (verwijzing naar: Tabelwaarde,Verpakkingssoort => AfasKnCodeTableValue)
                        'PkTp' => [
                            'alias' => 'package_type',
                        ],
                        // Vervoerder (verwijzing naar: Vervoerder => AfasKnTransporter)
                        'TrPt' => [
                            'alias' => 'shipping_company',
                        ],
                        // Dienst (verwijzing naar: Dienst => AfasKnShippingService)
                        'SsId' => [
                            'alias' => 'shipping_service',
                        ],
                        // Verwerking order (verwijzing naar: Tabelwaarde,Verwerking order => AfasKnCodeTableValue)
                        // Values:  1:Pakbon, factuur na levering   2:Pakbon en factuur   3:Factuur, levering na vooruitbetaling   4:Pakbon, geen factuur   5:Pakbon, factuur via nacalculatie   6:Pakbon en factuur, factuur niet afdrukken of verzenden   7:Aanbetalen, levering na aanbetaling
                        'OrPr' => [
                            'alias' => 'order_processing',
                        ],
                        // Bedrag aanbetalen
                        'AmDp' => [
                            'type' => 'decimal',
                        ],
                        // Vertegenwoordiger (verwijzing naar: Vertegenwoordiger => AfasKnRepresentative)
                        'VeId' => [],
                        // Afleveradres (verwijzing naar: Adres => AfasKnBasicAddress)
                        'DlAd' => [
                            'type' => 'long',
                        ],
                        // Omschrijving afleveradres
                        'ExAd' => [
                            'alias' => '',
                        ],
                        // Order blokkeren
                        'FxBl' => [
                            'alias' => 'block_order',
                            'type' => 'boolean',
                        ],
                        // Uitleverbaar
                        'DlYN' => [
                            'type' => 'boolean',
                        ],
                    ],
                ];
                break;

            case 'FbSalesLines':
                $info = [
                    'objects' => [
                        'FbOrderBatchLines' => 'batch_line_item',
                        'FbOrderSerialLines' => 'serial_line_item',
                    ],
                    'fields' => [
                        // Type item (verwijzing naar: Tabelwaarde,Itemtype => AfasKnCodeTableValue)
                        // Values:  1:Werksoort   10:Productie-indicator   11:Deeg   14:Artikeldimensietotaal   2:Artikel   3:Tekst   4:Subtotaal   5:Toeslag   6:Kosten   7:Samenstelling   8:Cursus
                        'VaIt' => [
                            'alias' => 'item_type',
                        ],
                        // Itemcode
                        'ItCd' => [
                            'alias' => 'item_code',
                        ],
                        // Omschrijving
                        'Ds' => [
                            'alias' => 'description',
                        ],
                        // Btw-tariefgroep (verwijzing naar: Btw-tariefgroep => AfasKnVatTarifGroup)
                        'VaRc' => [
                            'alias' => 'vat_type',
                        ],
                        // Eenheid (verwijzing naar: Eenheid => AfasFbUnit)
                        'BiUn' => [
                            'alias' => 'unit_type',
                        ],
                        // Aantal eenheden
                        'QuUn' => [
                            'alias' => 'quantity',
                            'type' => 'decimal',
                        ],
                        // Lengte
                        'QuLe' => [
                            'alias' => 'length',
                            'type' => 'decimal',
                        ],
                        // Breedte
                        'QuWi' => [
                            'alias' => 'width',
                            'type' => 'decimal',
                        ],
                        // Hoogte
                        'QuHe' => [
                            'alias' => 'height',
                            'type' => 'decimal',
                        ],
                        // Aantal besteld
                        'Qu' => [
                            'alias' => 'quantity_ordered',
                            'type' => 'decimal',
                        ],
                        // Aantal te leveren
                        'QuDl' => [
                            'alias' => 'quantity_deliver',
                            'type' => 'decimal',
                        ],
                        // Prijslijst (verwijzing naar: Prijslijst verkoop => AfasFbPriceListSale)
                        'PrLi' => [
                            'alias' => 'price_list',
                        ],
                        // Magazijn (verwijzing naar: Magazijn => AfasFbWarehouse)
                        'War' => [
                            'alias' => 'warehouse',
                        ],
                        // Dienstenberekening
                        'EUSe' => [
                            'type' => 'boolean',
                        ],
                        // Gewichtseenheid (verwijzing naar: Tabelwaarde,Gewichtseenheid => AfasKnCodeTableValue)
                        // Values:  0:Geen gewicht   1:Microgram (Âµg)   2:Milligram (mg)   3:Gram (g)   4:Kilogram (kg)   5:Ton
                        'VaWt' => [
                            'alias' => 'weight_unit',
                        ],
                        // Nettogewicht
                        'NeWe' => [
                            'alias' => 'weight_net',
                            'type' => 'decimal',
                        ],
                        //
                        'GrWe' => [
                            'alias' => 'weight_gross',
                            'type' => 'decimal',
                        ],
                        // Prijs per eenheid
                        'Upri' => [
                            'alias' => 'unit_price',
                            'type' => 'decimal',
                        ],
                        // Kostprijs
                        'CoPr' => [
                            'alias' => 'cost_price',
                            'type' => 'decimal',
                        ],
                        // Korting toestaan (verwijzing naar: Tabelwaarde,Toestaan korting => AfasKnCodeTableValue)
                        // Values:  0:Factuur- en regelkorting   1:Factuurkorting   2:Regelkorting   3:Geen factuur- en regelkorting
                        'VaAD' => [],
                        // % Regelkorting
                        'PRDc' => [
                            'type' => 'decimal',
                        ],
                        // Bedrag regelkorting
                        'ARDc' => [
                            'type' => 'decimal',
                        ],
                        // Handmatig bedrag regelkorting
                        'MaAD' => [
                            'type' => 'boolean',
                        ],
                        // Opmerking
                        'Re' => [
                            'alias' => 'comment',
                        ],
                        // GUID regel
                        'GuLi' => [
                            'alias' => 'guid',
                        ],
                        // Artikeldimensiecode 1 (verwijzing naar: Artikeldimensiecodes => AfasFbStockDimLines)
                        'StL1' => [
                            'alias' => 'dimension_1',
                        ],
                        // Artikeldimensiecode 2 (verwijzing naar: Artikeldimensiecodes => AfasFbStockDimLines)
                        'StL2' => [
                            'alias' => 'dimension_2',
                        ],
                        // Direct leveren vanuit leverancier
                        'DiDe' => [
                            'alias' => 'direct_delivery',
                            'type' => 'boolean',
                        ],
                    ],
                ];
                break;

            case 'FbOrderBatchLines':
                $info = [
                    'fields' => [
                        // Partijnummer
                        'BaNu' => [
                            'alias' => 'batch_number',
                        ],
                        // Eenheid (verwijzing naar: Eenheid => AfasFbUnit)
                        'BiUn' => [
                            'alias' => 'unit_type',
                        ],
                        // Aantal eenheden
                        'QuUn' => [
                            'alias' => 'quantity_units',
                            'type' => 'decimal',
                        ],
                        // Aantal
                        'Qu' => [
                            'alias' => 'quantity',
                            'type' => 'decimal',
                        ],
                        // Factuuraantal
                        'QuIn' => [
                            'alias' => 'quantity_invoice',
                            'type' => 'decimal',
                        ],
                        // Opmerking
                        'Re' => [
                            'alias' => 'comment',
                        ],
                        // Lengte
                        'QuLe' => [
                            'alias' => 'length',
                            'type' => 'decimal',
                        ],
                        // Breedte
                        'QuWi' => [
                            'alias' => 'width',
                            'type' => 'decimal',
                        ],
                        // Hoogte
                        'QuHe' => [
                            'alias' => 'height',
                            'type' => 'decimal',
                        ],
                    ],
                ];
                break;

            case 'FbOrderSerialLines':
                $info = [
                    'fields' => [
                        // Serienummer
                        'SeNu' => [
                            'alias' => 'serial_number',
                        ],
                        // Eenheid (verwijzing naar: Eenheid => AfasFbUnit)
                        'BiUn' => [
                            'alias' => 'unit_type',
                        ],
                        // Aantal eenheden
                        'QuUn' => [
                            'alias' => 'quantity_units',
                            'type' => 'decimal',
                        ],
                        // Aantal
                        'Qu' => [
                            'alias' => 'quantity',
                            'type' => 'decimal',
                        ],
                        // Factuuraantal
                        'QuIn' => [
                            'alias' => 'quantity_invoice',
                            'type' => 'decimal',
                        ],
                        // Opmerking
                        'Re' => [
                            'alias' => 'comment',
                        ],
                    ],
                ];
                break;
        }

        // If we are not sure that the record will be newly inserted, we do not
        // want to have default values - because those will risk silently
        // overwriting existing values in AFAS.
        // Exception: those marked with '!'.
        foreach ($info['fields'] as $field => &$definition) {
            if (isset($definition['default!'])) {
                // This is always the default
                $definition['default'] = $definition['default!'];
                unset($definition['default!']);
            } elseif (!$inserting) {
                unset($definition['default']);
            }
        }

        // If no ID is specified, default AutoNum to True for inserts.
        if (isset($info['fields']['AutoNum'])
            && $fields_action === 'insert' && !isset($data['#id'])
        ) {
            $info['fields']['AutoNum']['default'] = true;
        }

        // If this type is being rendered inside a parent type, then it cannot
        // contain its parent type. (Example: knPerson can be inside knContact
        // and it can also contain knContact... except when it is being rendered
        // inside knContact.)
        if (isset($info['objects'][$parent_type])) {
            unset($info['objects'][$parent_type]);
        }

        // If the definition has address and postal address defined, and the
        // data has an address but no postal address set, then the default
        // becomes PadAdr = true.
        if (isset($info['fields']['PadAdr'])
            && isset($info['objects']['KnBasicAddressAdr'])
            && isset($info['objects']['KnBasicAddressPad'])
            && (!empty($data['KnBasicAddressAdr'])
                || !empty($data[$info['objects']['KnBasicAddressAdr']]))
            && (empty($data['KnBasicAddressPad'])
                || empty($data[$info['objects']['KnBasicAddressPad']]))
        ) {
            $info['fields']['PadAdr']['default'] = true;
        }

        return $info;
    }
}