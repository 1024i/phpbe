<?php
namespace lib\ip2country;


class ip2country extends \system\lib
{

    private $db = null;

    private $total_records = 0;

    private $countries = null;

    // 构造函数
    public function __construct()
    {
        $this->db = @fopen(PATH_ROOT . DS . 'libs' . DS . 'ip2country' . DS . 'ip.dat', 'rb');
        if (!$this->db) {
            $this->set_error('Can not open file: ip.dat');
            return false;
        }

        $stat = fstat($this->db);
        $this->total_records = $stat['size'] / 10; // 每条记录占据 10Byte 空间

        $this->countries = array(
            'AT' => 'Austria',
            'AU' => 'Australia',
            'CN' => 'China',
            'JP' => 'Japan',
            'TH' => 'Thailand',
            'IN' => 'India',
            'MY' => 'Malaysia',
            'KR' => 'South Korea',
            'HK' => 'Hong Kong',
            'TW' => 'Taiwan',
            'PH' => 'Philippines',
            'VN' => 'Vietnam',
            'FR' => 'France',
            'EU' => 'European Union',
            'DE' => 'Germany',
            'SE' => 'Sweden',
            'IT' => 'Italy',
            'GR' => 'Greece',
            'ES' => 'Spain',
            'GB' => 'United Kingdom',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'AE' => 'United Arab Emirates',
            'IL' => 'Israel',
            'UA' => 'Ukraine',
            'CZ' => 'Czech Republic',
            'RU' => 'Russia',
            'KZ' => 'Kazakhstan',
            'PT' => 'Portugal',
            'SA' => 'Saudi Arabia',
            'DK' => 'Denmark',
            'IR' => 'Iran',
            'NO' => 'Norway',
            'US' => 'United States',
            'CA' => 'Canada',
            'MX' => 'Mexico',
            'BM' => 'Bermuda',
            'A1' => 'Anonymous Proxy',
            'SY' => 'Syria',
            'KW' => 'Kuwait',
            'CY' => 'Cyprus',
            'JO' => 'Jordan',
            'CH' => 'Switzerland',
            'IQ' => 'Iraq',
            'TR' => 'Turkey',
            'RO' => 'Romania',
            'LB' => 'Lebanon',
            'A2' => 'Satellite Provider',
            'HU' => 'Hungary',
            'GE' => 'Georgia',
            'AZ' => 'Azerbaijan',
            'PS' => 'Palestinian Territories',
            'LT' => 'Lithuania',
            'OM' => 'Oman',
            'RS' => 'Serbia',
            'FI' => 'Finland',
            'BG' => 'Bulgaria',
            'SI' => 'Slovenia',
            'MD' => 'Moldova',
            'MK' => 'Macedonia',
            'EE' => 'Estonia',
            'LI' => 'Liechtenstein',
            'JE' => 'Jersey',
            'PL' => 'Poland',
            'HR' => 'Croatia',
            'BA' => 'Bosnia and Herzegowina',
            'LV' => 'Latvia',
            'KG' => 'Kyrgyzstan',
            'IE' => 'Ireland',
            'LY' => 'Libya',
            'AM' => 'Armenia',
            'YE' => 'Yemen',
            'BY' => 'Belarus',
            'GI' => 'Gibraltar',
            'LU' => 'Luxembourg',
            'SK' => 'Slovakia',
            'MT' => 'Malta',
            'DO' => 'Dominican Republic',
            'PR' => 'Puerto Rico',
            'VI' => 'United States Virgin Islands',
            'BO' => 'Bolivia',
            'NZ' => 'New Zealand',
            'SG' => 'Singapore',
            'ID' => 'Indonesia',
            'NP' => 'Nepal',
            'PG' => 'Papua New Guinea',
            'PK' => 'Pakistan',
            'AP' => 'Non-spec Asia-Pac Location',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'LC' => 'Saint Lucia',
            'NC' => 'New Caledonia',
            'AR' => 'Argentina',
            'DM' => 'Dominica',
            'BD' => 'Bangladesh',
            'TK' => 'Tokelau',
            'KH' => 'Cambodia',
            'MO' => 'Macau',
            'MV' => 'Maldives',
            'AF' => 'Afghanistan',
            'FJ' => 'Fiji',
            'MN' => 'Mongolia',
            'WF' => 'Wallis and Futuna',
            'QA' => 'Qatar',
            'NG' => 'Nigeria',
            'IS' => 'Iceland',
            'AL' => 'Albania',
            'BZ' => 'Belize',
            'UZ' => 'Uzbekistan',
            'SJ' => 'Svalbard and Jan Mayen',
            'ZA' => 'South Africa',
            'VE' => 'Venezuela',
            'CO' => 'Colombia',
            'EG' => 'Egypt',
            'CL' => 'Chile',
            'DZ' => 'Algeria',
            'PE' => 'Peru',
            'MA' => 'Morocco',
            'AO' => 'Angola',
            'SD' => 'Sudan',
            'EC' => 'Ecuador',
            'LK' => 'Sri Lanka',
            'TN' => 'Tunisia',
            'GT' => 'Guatemala',
            'UY' => 'Uruguay',
            'MM' => 'Myanmar',
            'CR' => 'Costa Rica',
            'KE' => 'Kenya',
            'ET' => 'Ethiopia',
            'PA' => 'Panama',
            'TZ' => 'Tanzania',
            'CI' => 'Cote D\'ivoire',
            'CM' => 'Cameroon',
            'SV' => 'El Salvador',
            'BH' => 'Bahrain',
            'TT' => 'Trinidad and Tobago',
            'GH' => 'Ghana',
            'PY' => 'Paraguay',
            'UG' => 'Uganda',
            'ZM' => 'Zambia',
            'HN' => 'Honduras',
            'GQ' => 'Equatorial Guinea',
            'JM' => 'Jamaica',
            'SN' => 'Senegal',
            'CD' => 'Democratic Republic of the Congo',
            'GA' => 'Gabon',
            'BN' => 'Brunei Darussalam',
            'CG' => 'Congo',
            'NA' => 'Namibia',
            'MU' => 'Mauritius',
            'ML' => 'Mali',
            'BF' => 'Burkina Faso',
            'MG' => 'Madagascar',
            'TD' => 'Chad',
            'HT' => 'Haiti',
            'BJ' => 'Benin',
            'NI' => 'Nicaragua',
            'LS' => 'Lesotho',
            'RW' => 'Rwanda',
            'NE' => 'Niger',
            'TJ' => 'Tajikistan',
            'ZW' => 'Zimbabwe',
            'MW' => 'Malawi',
            'GN' => 'Guinea',
            'BB' => 'Barbados',
            'ME' => 'Montenegro',
            'MR' => 'Mauritania',
            'SR' => 'Suriname',
            'SZ' => 'Swaziland',
            'TG' => 'Togo',
            'ER' => 'Eritrea',
            'GY' => 'Guyana',
            'CF' => 'Central African Republic',
            'SL' => 'Sierra Leone',
            'CV' => 'Cape Verde',
            'BI' => 'Burundi',
            'BT' => 'Bhutan',
            'DJ' => 'Djibouti',
            'AG' => 'Antigua and Barbuda',
            'GM' => 'Gambia',
            'LR' => 'Liberia',
            'SC' => 'Seychelles',
            'FO' => 'Faroe Islands',
            'GL' => 'Greenland',
            'GG' => 'Guernsey',
            'VA' => 'Vatican City',
            'IM' => 'Isle of Man',
            'MC' => 'Monaco',
            'SM' => 'San Marino',
            'TM' => 'Turkmenistan',
            'AX' => 'Aland Islands',
            'AD' => 'Andorra',
            'AN' => 'Netherlands Antilles',
            'VG' => 'British Virgin Islands',
            'AQ' => 'Antarctica',
            'AI' => 'Anguilla',
            'AS' => 'American Samoa',
            'AW' => 'Aruba',
            'BL' => 'Saint Barthelemy',
            'BV' => 'Bouvet Island',
            'BW' => 'Botswana',
            'CC' => 'Cocos (Keeling) Islands',
            'CK' => 'Cook Islands',
            'CU' => 'Cuba',
            'CX' => 'Christmas Island',
            'EH' => 'Western Sahara',
            'FK' => 'Falkland Islands (Malvinas)',
            'FM' => 'Federated States of Micronesia',
            'GD' => 'Grenada',
            'GF' => 'French Guiana',
            'GP' => 'Guadeloupe',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'HM' => 'Heard Island and McDonald Islands',
            'IO' => 'British Indian Ocean Territory',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Kitts and Nevis',
            'KP' => 'North Korea',
            'KY' => 'Cayman Islands',
            'LA' => 'Laos',
            'MF' => 'Saint Martin',
            'MH' => 'Marshall Islands',
            'MP' => 'Northern Mariana Islands',
            'MQ' => 'Martinique',
            'MS' => 'Montserrat',
            'MZ' => 'Mozambique',
            'NF' => 'Norfolk Island',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'PF' => 'French Polynesia',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn',
            'PW' => 'Palau',
            'RE' => 'Reunion',
            'SB' => 'Solomon Islands',
            'SH' => 'Saint Helena',
            'SO' => 'Somalia',
            'ST' => 'Sao Tome and Principe',
            'TC' => 'Turks and Caicos Islands',
            'TF' => 'French Southern and Antarctic Lands',
            'TL' => 'East Timor',
            'TO' => 'Tonga',
            'TV' => 'Tuvalu',
            'UM' => 'United States Minor Outlying Islands',
            'VC' => 'Saint Vincent and the Grenadines',
            'VU' => 'Vanuatu',
            'WS' => 'Samoa',
            'YT' => 'Mayotte'
       );
    }

    // 析构函数
    public function __destruct()
    {
        if ($this->db ! == null && is_resource($this->db)) fclose($this->db);
    }

    public function get_country($ip)
    {
        if ($this->has_error()) return $this->get_error();
        if (!preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", $ip)) return 'Invalid IP address';

        $ip = ip2long($ip);
        if ($ip < 0) $ip += 4294967296;

        $start_index = 0;
        $end_index = $this->total_records;
        $middle_index = 0;

        $ip1 = $ip2 = 0;
        while ($ip1 > $ip || $ip2 < $ip) {
            $middle_index = intval(($start_index + $end_index) / 2);

            //echo $start_index.'-'.$end_index.'-'.$middle_index;
            //echo '<br />';

            fseek($this->db, $middle_index * 10 - 10);

            $buffer = fread($this->db, 4);
            if (strlen($buffer) < 4) return 'System error';
            $ip1 = implode('', unpack('L', $buffer));
            if ($ip1 < 0) $ip1 += 4294967296;

            $buffer = fread($this->db, 4);
            if (strlen($buffer) < 4) return 'System error';
            $ip2 = implode('', unpack('L', $buffer));
            if ($ip2 < 0) $ip2 += 4294967296;

            // 查找成功
            if ($ip1 <= $ip && $ip2 >= $ip) {
                $buffer = fread($this->db, 2);
                if (strlen($buffer) < 2) return 'System error';
                return implode('', unpack('A*', $buffer));
            }

            if ($middle_index == $start_index) break;

            if ($ip1 > $ip) {
                $end_index = $middle_index;
                continue;
            }

            if ($ip2 < $ip) $start_index = $middle_index;
        }

        return 'Unknown';
    }


    public function get_country_full_name($ip)
    {
        $country = $this->get_country($ip);
        if (isset($this->countries[$country])) return $this->countries[$country];
        return 'Unknown';
    }

    public function convert($ip)
    {
        return $this->get_country_full_name($ip);
    }

    public function to_string()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $this->get_country_full_name($ip);
    }
}
