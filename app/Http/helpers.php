<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 1/9/17
 * Time: 3:46 PM
 */
use Faker\Generator as Faker;

/**
 *
 * Check array keys exist
 * For easier validation of required input for JSON data
 *
 * @param $array AssociativeArray The associative array to check keys for.
 * @param $expected_keys Array|AssociativeArray
 * @return bool keys exist
 *
 * Also recursive check.
 * Examples:
 * Simple true example:
 *  $array = ('test1'='val1', 'test2'=>'val2')
 *  $expected_keys = ('test1', 'test2')
 *  response: true
 * Simple false example:
 *  $array = ('test1'='val1', 'test2'=>'val2')
 *  $expected_keys = ('test1', 'test2','test3')
 *  response: false
 *
 * Recursive true example:
 *  $array = ('test1'=>array('rtest1'=>'rval1', 'rtest2'=>'rval2'), 'test2'=>'val1')
 *  $expected_keys = ('test1'=>array('rtest1','rtest2'), 'test2')
 *  response: true
 *
 * Note: $array['test3']='val3' could be included and this would still be true
 */
if ( ! function_exists('check_array_keys_exist'))
{
    function check_array_keys_exist($array, $expected_keys){
        $keys_exist = true;
        if(empty($array) || empty($expected_keys)){
            $keys_exist = false;
        }elseif(array_values($array) !== $array){ // Is the array associative
            $success = true;
            $array_keys = array_keys($expected_keys);
            foreach($array_keys as $index => $key){
                if(gettype($key) == 'string' && array_key_exists($key, $expected_keys) && gettype($expected_keys[$key]) == 'array'){
                    if(array_key_exists($key, $array)) {
                        $success = check_array_keys_exist($array[$key], $expected_keys[$key]);
                        if($success == false){
                            break;
                        }
                    }else{
                        $success = false;
                        break;
                    }
                }else{
                    $key = $expected_keys[$key];
                    if(!array_key_exists($key, $array)){
                        $success = false;
                        break;
                    }
                }
            }
            $keys_exist = $success;
        }else{
            $keys_exist = (count(array_intersect_key(array_flip($expected_keys), $array)) === count($expected_keys))?(true):(false);
        }

        return $keys_exist;
    }
}

if ( ! function_exists('generate_random_string'))
{
    function generate_random_string($length, $lowercase = true)
    {
        $random_string = '';

        for ($i=1;$i<=$length;$i++){
            // Alphabetical range
            $alph_from = 65;
            $alph_to = 90;

            // Numeric
            $num_from = 48;
            $num_to = 57;

            // Add a random num/alpha character
            $chr = rand(0,1)?(chr(rand($alph_from,$alph_to))):(chr(rand($num_from,$num_to)));
            if($lowercase === true) {
                $chr = rand(0, 1) ? (strtolower($chr)) : ($chr);
            }
            $random_string.=$chr;
        }

        return $random_string;
    }
}

if ( ! function_exists('echo_newline'))
{
    function echo_newline($count=1)
    {
        if(is_numeric($count)){
            while($count > 0){
                echo "<br/>\n\r";
                $count--;
            }
        }else {
            echo "<br/>\n\r";
        }
    }
}

if ( ! function_exists('pretty_print_r'))
{
    function pretty_print_r($input)
    {
        echo "<pre>";
        print_r($input);
        echo "</pre>";
    }
}

if ( ! function_exists('formatPhoneNumber'))
{
    function formatPhoneNumber($initial_phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9+]/','',$initial_phoneNumber);
        $match = preg_match('/[+]+/', $initial_phoneNumber);
        if(strlen($phoneNumber) > 10 || $match) {
            return $initial_phoneNumber;
        }
        else if(strlen($phoneNumber) == 10) {
            $areaCode = substr($phoneNumber, 0, 3);
            $nextThree = substr($phoneNumber, 3, 3);
            $lastFour = substr($phoneNumber, 6, 4);

            $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
        }
        else if(strlen($phoneNumber) == 7) {
            $nextThree = substr($phoneNumber, 0, 3);
            $lastFour = substr($phoneNumber, 3, 4);

            $phoneNumber = $nextThree.'-'.$lastFour;
        }

        return $phoneNumber;
    }
}

if ( ! function_exists('mw_server_call'))
{
    function mw_server_call($method, $data, $api_key){
        $json_data = json_encode($data);
        $bodyData = array (
            'api_key' => $api_key,
            'method'=>$method,
            'json' => $json_data
        );

        $bodyStr = http_build_query($bodyData);
        $url = config('app.mw_url');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: '.strlen($bodyStr),
            'HeaderName: '.config('app.url')
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyStr);
        curl_setopt($ch, CURLOPT_COOKIE, "api_key_cookie=$api_key;");
        $result = curl_exec($ch);
        return $result;
    }
}

// --------------------------------------------------------------------
if ( ! function_exists('custom_empty'))
{
    function custom_empty($array, $key){
        if(!empty($array) && !empty($key) && gettype($array) == 'array' && gettype($key) == 'string') {
            return (!empty($array[$key])) ? ($array[$key]) : (FALSE);
        }else{
            return false;
        }
    }
}
// --------------------------------------------------------------------

if(!function_exists('numberToWords')){
    function numberToWords($number)
    {
        $result = array();
        $tens = floor($number / 10);
        $units = $number % 10;

        $words = array
        (
            'units' => array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'),
            'tens' => array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety')
        );

        if ($tens < 2)
        {
            $result[] = $words['units'][$tens * 10 + $units];
        }

        else
        {
            $result[] = $words['tens'][$tens];

            if ($units > 0)
            {
                $result[count($result) - 1] .= '-' . $words['units'][$units];
            }
        }

        if (empty($result[0]))
        {
            $result[0] = 'Zero';
        }

        return trim(implode(' ', $result));
    }
}

/**
 * Returns true/false for the provided password as to whether or not
 * it fits the current software requirements for password complexity
 *
 * Requirements:
 * -Must have at least 1 letter
 * -Only contains letters, numbers, and a defined set of special characters. Namely no special characters from non english languages
 * -Password must be inclusively between 8(min) and 32(max) characters in length
 * -Must score at least a 3 for password score. Gaining points for uppercase, lowercase, numbers, special characters, and lengths > min length
 */
if ( ! function_exists('validate_password_complexity'))
{
    function validate_password_complexity($password){
        //Make sure the password is a string
        if(gettype($password) !== 'string'){
            return false;
        }

        $PASSWORD_SCORE = 0;
        $REQUIRED_PASSWORD_SCORE = 3;
        $MIN_PASSWORD_LENGTH = 8;
        $MAX_PASSWORD_LENGTH = 32;
        $password_length = strlen($password);

        /*Regular expression declaration*/
        //want or maybe want
        $has_letter = '/[a-zA-Z]/';
        $has_uppercase = '/[A-Z]/';
        $has_lowercase = '/[a-z]/';
        $has_number = '/[0-9]/';
        $special_character = '/[\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\\\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}\~]/';
        $only_valid_characters = '/^[A-Za-z0-9\!\"\#\$\%\&\'\(\)\*\+\,\-\.\/\\\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}\~]+/';

        //dont want
        $three_sequential_letters = '/(.)\1\1/';
        $space_tab_linebreaks = '/[^\S]/';
        /**/

        //Check the length of the password
        if($password_length < $MIN_PASSWORD_LENGTH || $password_length > $MAX_PASSWORD_LENGTH){
            return false;
        }

        //Make sure the password is only valid characters
        if(!preg_match($only_valid_characters, $password) || preg_match($space_tab_linebreaks, $password)){return false;}

        //Make sure the password doesn't have more than 2 sequential characters
        if(preg_match($three_sequential_letters, $password)){return false;}

        //Make sure the password has at least 1 letter
        if(!preg_match($has_letter, $password)){return false;}


        //Does the password contain an uppercase letter?
        if(preg_match($has_uppercase, $password)){$PASSWORD_SCORE++;}
        //Does the password contain a lowercase letter?
        if(preg_match($has_lowercase, $password)){$PASSWORD_SCORE++;}
        //Does the password contain a number?
        if(preg_match($has_number, $password)){$PASSWORD_SCORE++;}
        //Does the password contain a special character?
        if(preg_match($special_character, $password)){$PASSWORD_SCORE++;}

        //Penalize for less than 3 character types
        if($PASSWORD_SCORE < 3){ $PASSWORD_SCORE--; }

        //Reward for longer passwords
        if($password_length > $MIN_PASSWORD_LENGTH){
            $PASSWORD_SCORE += floor(($password_length-$MIN_PASSWORD_LENGTH)/2);
        }

        //Return whether or not the password passed the tests
        return($PASSWORD_SCORE >= $REQUIRED_PASSWORD_SCORE);
    }
}


if ( ! function_exists('generate_salt'))
{
    function generate_salt(){
        $length = 22; // 22 Chars long key
        $salt = "";

        for ($i=1;$i<=$length;$i++){
            // Alphabetical range
            $alph_from = 65;
            $alph_to = 90;

            // Numeric
            $num_from = 48;
            $num_to = 57;

            // Add a random num/alpha character
            $chr = rand(0,1)?(chr(rand($alph_from,$alph_to))):(chr(rand($num_from,$num_to)));
            if (rand(0,1)) $chr = strtolower($chr);
            $salt.=$chr;
        }

        return $salt;
    }
}

if ( ! function_exists('trigger_custom_error'))
{
    function trigger_custom_error($error_message){
        $bt = debug_backtrace();
        $line = $bt[0]['line'];
        $file = $bt[0]['file'];
        $msg = "An error was encounted.";
        $msg = $msg . "\r\n";
        $msg = $msg . " String - " . $error_message;
        $msg = $msg . "\r\n";
        $msg = $msg . " Error File - " . $file;
        $msg = $msg . "\r\n";
        $msg = $msg . " Error Line - " . $line;
        $msg = $msg . "\r\n";
//        error_log($msg, 1, "msmckeller@realequitymanagement.com, rchaslag@realequitymanagement.com");
        trigger_error($error_message);
//        error_log($msg, 1, "msmckeller@realequitymanagement.com");
    }
}


if(! function_exists('castObject')){
    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    function castObject($destination, $sourceObject)
    {
        if (is_string($destination)) {
            $destination = new $destination();
        }
        $sourceReflection = new ReflectionObject($sourceObject);
        $destinationReflection = new ReflectionObject($destination);
        $sourceProperties = $sourceReflection->getProperties();
        foreach ($sourceProperties as $sourceProperty) {
            $sourceProperty->setAccessible(true);
            $name = $sourceProperty->getName();
            $value = $sourceProperty->getValue($sourceObject);
            if ($destinationReflection->hasProperty($name)) {
                $propDest = $destinationReflection->getProperty($name);
                $propDest->setAccessible(true);
                $propDest->setValue($destination,$value);
            } else {
                $destination->$name = $value;
            }
        }
        return $destination;
    }
    
}

if(! function_exists('cleanObjectName')){
    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    function cleanObjectName($objectNameWithPath)
    {
        $explode = explode('\\', $objectNameWithPath);
        return $explode[count($explode)-1];
    }

}

if(! function_exists('stripColumnName')){
    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    function stripColumnName($fullName)
    {
        $explode = explode('.', $fullName);
        return $explode[count($explode)-1];
    }

}

if(! function_exists('outputBacktrace')){
    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    function outputBacktrace($trace, $includeObject = false, $includeArgs = false)
    {
        foreach($trace as $index=>$traceInstance){
            echo "TRACE INDEX: $index";echo_newline();
            outputBacktraceIndex($trace, $index, $includeObject, $includeArgs);
        }
        return;
    }

}

if(! function_exists('outputBacktraceIndex')){
    /**
     * Class casting
     *
     * @param string|object $destination
     * @param object $sourceObject
     * @return object
     */
    function outputBacktraceIndex($trace, $index, $includeObject = false, $includeArgs = false)
    {
        $traceOutput = $trace;
        if($includeObject !== true){
            unset($traceOutput[$index]['object']);
        }
        if($includeArgs!== true){
            unset($traceOutput[$index]['args']);
        }
        pretty_print_r($traceOutput[$index]);
        return;
    }

}

if(! function_exists('nowTimestamp')){
    /**
     * Class casting
     *
     * @return string 'Y-m-d H:i:s' of now
     */
    function nowTimestamp()
    {
        $dateTime = new \DateTime('now');
        return $dateTime->format('Y-m-d H:i:s');
    }

}

if(! function_exists('getAllClassesInDirectory')){
    /**
     * Class casting
     *
     * @return string 'Y-m-d H:i:s' of now
     */
    function getAllClassesInDirectory($path)
    {
        $fqcns = array();

        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0]) {
                    $index += 2; // Skip class keyword and whitespace
                    $fqcns[] = $namespace.'\\'.$tokens[$index][1];
                }
            }
        }

        return $fqcns;
    }

}

if(! function_exists('getDateIntervalInSeconds')){
    /**
     * @param DateInterval $dateInterval
     * @return int the total number of seconds in the interval
     */
    function getDateIntervalInSeconds(DateInterval $dateInterval){
        $intervalInSeconds = (new DateTime())->setTimeStamp(0)->add($dateInterval)->getTimeStamp();
        return $intervalInSeconds;
    }

}


if(! function_exists('checkIfFkExists')){
    /**
     * @param DateInterval $dateInterval
     * @return int the total number of seconds in the interval
     */
    function checkIfFKExists(Illuminate\Database\Schema\Blueprint $table, $fkName){
        $tableName = $table->getTable();
        $conn = Illuminate\Support\Facades\Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->listTableDetails($tableName);

        if (! $doctrineTable->hasIndex($fkName) && !$doctrineTable->hasForeignKey($fkName))
        {
            return false;
        }else{
            return true;
        }
    }

}

if(! function_exists('cleanDateTimeBetween')){
    /**
     * @param $start
     * @param $end
     * @return mixed
     */
    function cleanDateTimeBetween($start, $end){
        $faker = new \Faker\Generator();
        $faker->addProvider(new \Faker\Provider\DateTime($faker));
        $date = $faker->dateTimeBetween($start,$end);
        $dateRanges = array(
            ['start'=>'2017-03-12', 'end'=>'2017-03-13']
        );
        foreach($dateRanges as $dateInfo) {
            if (!(new \DateTime($date->format('Y-m-d H:i:s')) > new \DateTime($dateInfo['start']) && new \DateTime($date->format('Y-m-d H:i:s')) < new \DateTime($dateInfo['end']))){
                return $date;
            }else{
                return cleanDateTimeBetween($start, $end);
            }
        }
    }

}

if(! function_exists('redirectEleaseProfile')){
    /**
     * @param $start
     * @param $end
     * @return mixed
     */
    function redirectEleaseProfile($customer){
        return redirect(env('TEAM_URL') . '/customer_profile/'.$customer->id);
    }

}


