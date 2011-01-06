<?php

/**
 * File contains helper functions
 *
 * @package    coslib
 */

// {{{ function get_zero_or_positive($int, $max = null);
/**
 * function for checking if var is int larger than zero
 * 
 * @param   mixed   $int the var to check
 * @param   int     $max max size of integer
 * @return  int     0 or positive integer
 */
function get_zero_or_positive($int, $max = null){
    $int = (int)$int;
    if (!is_int($int)){
        $zero = true;
    }
    if (isset($max)){
        if ($int > $max){
            $zero = true;
        }
    }

    //negativ int
    if ($int < 0) {
        $zero = true;
    }
    if (isset($zero)) {
        return 0;
    } else {
        return $int;
    }
}

// }}}
// {{{ function get_file_list($dir)

/**
 * function for getting a file list of a directory (. and .. will not be
 * collected
 *
 * @param   string  the path to the directory where we want to create a filelist
 * @param   array   if $options['dir_only'] isset only return directories.
 *                  if $options['search'] isset then only dirs containing
 *                      search string will be returned
 * @return  array   entries of all files <code>array (0 => 'file.txt', 1 => 'test.php');</code>
 */
function get_file_list($dir, $options = null){
    if (!file_exists($dir)){
        return false;
    }
    $d = dir($dir);
    $entries = array();
    while (false !== ($entry = $d->read())) {
        if ($entry == '..') continue;
        if ($entry == '.') continue;
        if (isset($options['dir_only'])){
            if (is_dir($dir . "/$entry")){
                if (isset($options['search'])){
                    if (strstr($entry, $options['search'])){
                       $entries[] = $entry;
                    }
                } else {
                    $entries[] = $entry;
                }
            }
        } else {
            $entries[] = $entry;
        }
    }
    $d->close();
    return $entries;
}

// }}}
// {{{ get_file_list_recursive
/**
 *
 * found on php.net
 */
function get_file_list_recursive($start_dir) {

    $files = array();
    if (is_dir($start_dir)) {
        $fh = opendir($start_dir);
        while (($file = readdir($fh)) !== false) {
            // loop through the files, skipping . and .., and recursing if necessary
            if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
            $filepath = $start_dir . '/' . $file;
            if ( is_dir($filepath) )
                $files = array_merge($files, get_file_list_recursive($filepath));
            else
                array_push($files, $filepath);
        }
        closedir($fh);
    } else {
        // false if the function was called with an invalid non-directory argument
        $files = false;
    }

    return $files;
}
// }}}
// {{{ function include_module($module)
/**
 * function for including a modules view and model file
 *
 * @param   string  the name of the module to include
 *                  includes the view and the model file for module.
 */
function include_module($module, $options = null){
    static $modules = array ();
    if (isset($modules[$module])){
        // module has been included
        return;
    }

    $module_path = register::$vars['coscms_base'] . '/modules/' . $module;
    $ary = explode('/', $module);
    $last = array_pop($ary);
    $model_file = $module_path . '/' . "model.$last.inc";  
    $view_file = $module_path . '/' . "view.$last.inc";
    $ary = explode('/', $module);
    lang::loadModuleLanguage($ary[0]);
    moduleLoader::getModuleIniSettings($ary[0]);

    include_once $model_file;
    if (file_exists($view_file)){
        include_once $view_file;
    }
}

// }}}
// {{{ include_model($module)
/**
 *
 * @param   string   $module module to include e.g. (content/article)
 */
function include_model($module){
    $module_path = 'modules/' . $module;
    $ary = explode('/', $module);
    $last = array_pop($ary);
    $model_file = $module_path . '/' . "model.$last.inc";
    include_once $model_file;
}

// }}}
// {{{ include_controller($controller, $options)
/**
 *
 * @param string    controller to include (e.g. content/article/add)
 * @param array     $options
 */
function include_controller($controller, $options = null){
    $module_path = register::$vars['coscms_base']  . '/modules/' . $controller;
    $controller_file = $module_path . '.php';
    include_once $controller_file;
}

// }}}
// {{{ function create_seo_title($title)
/**
 * function for creating a seo friendly title
 *
 * @param   string   the title of the url to be created
 * @return  string   the title with _ instead of spaces ' '
 */
function create_seo_title($title){
    $title = explode(' ', $title);
    $title = cos_url_encode(strtolower(implode($title, '_')));
    return $title;
}

// }}}
// {{{ function create_link($url, $title, $description)
/**
 * function for creating a link
 *
 * @param   string  the url to create the link from
 * @param   string  the title of the link
 * @param   boolean if true we only return the url and not the html link
 * @return  string  the <code><a href='url'>title</></code> tag
 */
function create_link($url, $title, $return_url = false, $css = null){
    if (class_exists('rewrite_manip')) {
        $alt_uri = rewrite_manip::getRowFromRequest($url);
        if (isset($alt_uri)){
            $url = $alt_uri; //$row['rewrite_uri'];
        }
    }

    if ($return_url){
        return $url;
    }
    if ($_SERVER['REQUEST_URI'] == $url){
        return "<a href=\"$url\" class=\"current\">$title</a>";
    }
    if ($css){
        $link = "<a href=\"$url\" class=\"$css\">$title</a>";
        return $link;
    }


    return "<a href=\"$url\">$title</a>";
    
}

// }}}
// {{{ function create_link($url, $title, $description)
/**
 * function for creating a link
 *
 * @param   string  the url to create the link from
 * @param   string  the title of the link
 * @param   boolean if true we only return the url and not the html link
 * @return  string  the <code><a href='url'>title</></code> tag
 */
function create_image_link($url, $href_image, $options = null){
    $str = '';
    if (isset($options['alt'])) $str.= " alt = \"$options[alt]\" ";
    if (isset($options['width'])) $str.= " width = \"$options[width]\" ";
    if (isset($options['height'])) $alt = $options['height'];
    return "<a href=\"$url\"><img $str src=\"$href_image\" /></a>";
}

function create_image($href_image, $options = null){
    $str = '';
    if (isset($options['alt'])) $str.= " alt = \"$options[alt]\" ";
    if (isset($options['width'])) $str.= " width = \"$options[width]\" ";
    if (isset($options['height'])) $alt = $options['height'];
    return "<img $str src=\"$href_image\" />";
}

// }}}
// {{{ function view_drop_down_db($name, $table, $field, $id, $selected = null))
/**
 * function for creating a select dropdown from a database table.
 *
 * @param   string  the name of the select filed
 * @param   string  the database table to select from
 * @param   string  the database field which will be used as name of the select element
 * @param   int     the database field which will be used as id of the select element
 * @param   int     the element which will be selected
 * @param   array   array of other non db options
 * @param   string  behavior e.g. onChange="this.form.submit()"
 * @return  string  the select element to be added to a form
 */
function view_drop_down_db($name, $table, $field, $id, $selected=null, $extras = null, $behav = null){
    $db = new db();
    $dropdown = "<select name=\"$name\" ";
    if (isset($behav)){
        $dropdown.= $behav;
        
    }
    $dropdown.= ">\n";
    $rows = $db->selectAll($table);
    if (isset($extras)){
        $rows = array_merge($extras, $rows);
    }
    foreach($rows as $row){
        if ($row[$id] == $selected){
            $s = ' selected';
        } else {
            $s = '';
        }

        $dropdown.= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
    }
    $dropdown.= '</select>'."\n";
    return $dropdown;
}

// }}}
// {{{ function view_drop_down($name, $rows, $field, $id, $selected= null)
/**
 *
 * @param   string  the name of the select field
 * @param   array   the rows making up the ids and names of the select field
 * @param   string  the field which will be used as name of the select element
 * @param   int     the field which will be used as id of the select element
 * @param   int     the element which will be selected
 * @return  string  the select element to be added to a form
 */
function view_drop_down($name, $rows, $field, $id, $selected=null, $behav = null){
    $dropdown = "<select name=\"$name\" ";
    if (isset($behav)){
        $dropdown.= $behav;

    }
    $dropdown.= ">\n";
    foreach($rows as $row){
        if ($row[$id] == $selected){
            $s = ' selected';
        } else {
            $s = '';
        }

        $dropdown .= '<option value="'.$row[$id].'"' . $s . '>'.$row[$field].'</option>'."\n";
    }
    $dropdown .= '</select>'."\n";
    return $dropdown;
}

// }}}
// {{{ function mail_utf8($to, $subject, $message, $from)

/**
 * function for mailing
 *
 * @param   string  to whom are we gonna send the email
 * @param   string  the subject of the email
 * @param   string  the message of the email
 * @param   string  from the sender of the email
 * @return  int     1 on success 0 on error
 */
function mail_utf8($to, $subject, $message, $from, $reply_to=null) {
    // create headers for sending email
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers.= 'Content-type: text/plain; charset=UTF-8' . "\r\n";
    $headers.= "From: $from\r\n";
    if (!$reply_to){
        $reply_to = $from;
    }
    $headers.= "Reply-To: $reply_to" . "\r\n";
    $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    $message = wordwrap($message, 70);
    
    if (isset(register::$vars['coscms_main']['send_mail'])){

        if (isset(register::$vars['coscms_main']['smtp_mail'])){
            $res = mail_smtp ($to, $subject, $message, $from, $reply_to);
        } else {
            $res = mail($to, $subject, $message, $headers);
        }

        $log = "TO: $to\n";
        $log.= "SUBJECT: $subject\n";
        $log.= "MESSAGE: $message\n";
        $log.= "HEADERS: $headers\n";
        $log.= "RESULT $res\n";

        if (register::$vars['coscms_main']['debug']){
            $log_file = _COS_PATH . '/logs/coscms.log';
            error_log($log, 3, $log_file);
        }
        return $res;
    } else {
        $log = "\nSending mail to: $to\n";
        $log.= "Subject: $subject\n";
        $log.= "Message: $message\n";
        $log.= "Header: $headers\n";
        $log_file = _COS_PATH . '/logs/coscms.log';
        error_log($log, 3, $log_file);
        return 1;
    }
}

// }}} 
// {{{ mail_smtp
/**
 * method for sending mails via smtp
 */
function mail_smtp ($recipient, $subject, $message, $from, $reply_to/*$headers = null*/){
    include_once('Mail.php');
    include_once('Mail/mime.php');
    
    $from = register::$vars['coscms_main']['smtp_params_sender'];                                            // Your email address
    $recipient = "<$recipient>";                               // The Recipients name and email address
    //$subject = "Another test Email";                                                // Subject for the email
    //$text = 'This is a text message.';                                      // Text version of the email
    //$html = '<html><body><p>This is a html message!</p></body></html>';      // HTML version of the email
    $crlf = "\n";


    $headers = array(
        'From'          => $from,
        'Return-Path'   => $from,
        'Reply-To'      => $reply_to,
        'Subject'       => $subject,//'=?UTF-8?B?'.base64_encode($subject).'?=',//$subject,
        //Content-type: text/plain; charset=UTF-8'
        'Content-type' => 'text/plain; charset=UTF-8'
    );



    // Creating the Mime message
    $mime = new Mail_mime($crlf);



    // Setting the body of the email
    $mime->setTXTBody($message);
    //$mime->setHTMLBody($html);

    // Add an attachment

    //$file = "Hello World!";
    //$file_name = "Hello text.txt";
    //$content_type = "text/plain";
    //$mime->addAttachment ($file, $content_type, $file_name, 0);

    // Set body and headers ready for base mail class
    $body = $mime->get(array('text_charset' => 'utf-8'));
    $headers = $mime->headers($headers);



    // SMTP authentication params
    $smtp_params = array();
    $smtp_params["host"]     = register::$vars['coscms_main']['smtp_params_host']; //"ssl://smtp.gmail.com";
    $smtp_params["port"]     = register::$vars['coscms_main']['smtp_params_port'];
    $smtp_params["auth"]     = true; //register::$vars['coscms_main']['smtp_params_auth'];
    $smtp_params["username"] = register::$vars['coscms_main']['smtp_params_username'];
    $smtp_params["password"] = register::$vars['coscms_main']['smtp_params_password'];

// Sending the email using smtp
    $mail =& Mail::factory("smtp", $smtp_params);
    $result = $mail->send($recipient, $headers, $body);
    return $result;
}
// }}}
// {{{ function cos_htmlentites($values)

/**
 * function for creating rewriting htmlentities for safe display on screen
 *
 * @param   array|string    value(s) to transform
 * @return  array|string    value(s) transformed
 */
function cos_htmlentities($values){
    if (is_array($values)){
        foreach($values as $key => $val){
            $values[$key] = htmlentities($val, ENT_COMPAT, 'UTF-8');
        }
    } else if (is_string($values)) {
        $values =  htmlentities($values, ENT_COMPAT, 'UTF-8');
    } else {
        $values = '';
    }
    return $values;
}

// }}}
// {{{ function cos_htmlspecialchars($values)

/**
 * function for creating rewriting htmlspecialchars for safe display on screen
 *
 * @param   array|string    value(s) to transform
 * @return  array|string    value(s) transformed
 */
function cos_htmlspecialchars($values){
    if (is_array($values)){
        foreach($values as $key => $val){
            $values[$key] = htmlspecialchars($val, ENT_COMPAT, 'UTF-8');
        }
    } else {
        $values =  htmlspecialchars($values, ENT_COMPAT, 'UTF-8');
    }
    return $values;
}

// }}}
// {{{ timestamp_to_days($timestamp)
/**
 * method for transforming a timestamp to days
 * @param   string  timestamp
 * @return  int     days
 */
function timestamp_to_days($updated){
    $diff = time() - strtotime($updated);
    $diff / 60 / 60 / 24;
}
// }}}

// {{{
function get_filtered_content ($filter, $content){
    
    if (!is_array($filter)){
        $class_path = _COS_PATH . "/modules/filter_$filter/$filter.inc";
        include_once $class_path;
        $class = 'filter' . ucfirst($filter);
        $filter_class = new $class;

        if (is_array($content)){
            foreach ($content as $key => $val){
                $content[$key] = $filter_class->filter($val);
            }
        } else {
            $content = $filter_class->filter($content);
        }
        
        return $content;
    }

    if (is_array ($filter)){

        foreach($filter as $key => $val){

            $class_path = _COS_PATH . "/modules/filter_$val/$val.inc";
            include_once $class_path;
            $class = 'filter' . ucfirst($val);
            $filter_class = new $class;
            if (is_array($content)){
                foreach ($content as $key => $val){
                    $content[$key] = $filter_class->filter($val);
                }
            } else {
                $content = $filter_class->filter($content);
            }
        }
        return $content;
    }
    return '';
}

// }}}
// {{{ get_module_ini($values)
/**
 * method for getting a modules ini settings
 */
function get_module_ini($value){
    if (!isset(register::$vars['coscms_main']['module'][$value])){
        return null;
    }
    if (register::$vars['coscms_main']['module'][$value] == '0'){
        return null;
    }
    
    return register::$vars['coscms_main']['module'][$value];
    
}
// }}}
// {{{ get_main_ini($value)
/**
 * method for getting a main ini setting
 *
 * @param   string  ini setting to get
 * @return  mixed   the setting
 */
function get_main_ini($value){
    if (!isset(register::$vars['coscms_main'][$value])){
        return null;
    }

    if (register::$vars['coscms_main'][$value] == '0'){
        return null;
    }
    return register::$vars['coscms_main'][$value];
}

// }}}
// {{{ isvalue($var)
/**
 * method used for checking if something is a value
 * is something is sat and has values
 *
 * @param   mixed
 * @return  boolean
 */
function isvalue($var){
    if (isset($var) && !empty($var)){
        return true;
    }
    return false;
}

// }}}
// {{{ get_include_contents ($filename)
/**
 * function for getting content from a file
 * used as a very simple template function
 */
function get_include_contents($filename, $vars = null) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}

// }}}
// {{{ substr2 ($str, $length, $min)
/** 
 * Substring without losing word meaning and
 * tiny words (length 3 by default) are included on the result.
 *  "..." is added if result do not reach original string length
 * Found on php.net
 *
 * @param   string  string to operate on
 * @param   int     length to cut at
 * @param   int     size of minimum word
 * @return  string  string transformed
 */
function substr2($str, $length, $minword = 3)
{
    $sub = '';
    $len = 0;

    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);

        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }

    return $sub . (($len < strlen($str)) ? ' ... ' : '');
}

// }}}
// {{{ send_cache_headers()
/**
 * method for sending cache headers when e.g. sending images from db
 */
function send_cache_headers (){

    // one month
    $expires = 60*60*24*30;
    header("Pragma: public");
    header("Cache-Control: maxage=".$expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    
}

// }}}
// {{{ get_module_path
/**
 * method for getting a path to a module
 *
 * @param   string  the module
 * @return  string  the module path
 */
function get_module_path ($module){
    return _COS_PATH . '/modules/' . $module;
}

// }}}
// {{{ save_post($id)
/**
 * simple method for saving $_POST vars to session
 *
 * @param   string  id of the post to save
 */
function save_post ($id){
     $_SESSION[$id] = $_POST;
}

// }}}
// {{{ load_post($id)
/**
 * method for loading $_POST vars from session
 * @param   string  id of the post to load
 */
function load_post($id){
    $_POST = @$_SESSION[$id];
}
// }}}
// {{{ simple_template ($file)
/**
 * simple template method for collecting a string from a file
 */
function simple_template ($file){
    ob_start();
    include $file;
    $parsed = ob_get_contents();
    ob_end_clean();
    return $parsed;
}
// }}}
// {{{ class for validating
/**
 *
 * class for validating email and and emailAndDomain
 */
class cosValidate {
    /**
     * method for just validating email
     * @param   string  email
     * @return  boolean 
     */
    public static function email ($email){
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return false;
        }
        return true;
    }
    
    /**
     * method for just validating email
     * @param   string  email
     * @return  boolean 
     */
    public static function urlWithFilter ($url){
        require_once 'Validate.php';
        if (!filter_var($url, FILTER_VALIDATE_URL)){
            return false;
        }
        return true;
    }

    /**
     * method for just validating email
     * @param   string  email
     * @return  boolean
     */
    public static function url ($url){
        require_once 'Validate.php';
        $schemes = array ('http', 'https');
        if (!Validate::uri($url, array('allowed_schemes' => $schemes))){
            return false;
        }
        return true;
    }
    
    /**
     * method for vaildating the email the emails domain
     * @param   string  email
     * @return  boolean 
     */
    public static function validateEmailAndDomain ($email){
        require_once 'Validate.php';

        if (Validate::email($email, array('check_domain' => 'true'))) {
            return true;
        }
        return false;
    }
}
// }}}
// {{{ cos_url_encode($string)
/**
 * function for url encoding a utf8 string
 * @param   string  the utf8 string to encode
 * @return  string  the utf8 encoded string
 */
function cos_url_encode($string){
    return urlencode(utf8_encode($string));
}

// }}}
// {{{ cos_url_decode
/**
 * function for decoding a url8 encoded string
 * @param   string  the string to decode
 * @return  string  the decoded utf8 string
 */
function cos_url_decode($string){
    return utf8_decode(urldecode($string));
}
// }}}


function get_profile_link (&$user){
    static $profile_object;

    if (!isset($profile_object)){
        $profile_system = get_main_ini('profile_module');
        if (!isset($profile_system)){
            return $user['username'];
        }

        include_model ($profile_system);

        $profile_object = moduleLoader::modulePathToClassName($profile_system);
        $profile_object = new $profile_object();        
        $link = $profile_object->createProfileLink($user);
        return $link;
    }

    return $profile_object->createProfileLink($user);
}

/**
 * function for creating prg pattern with ease
 */

function simple_prg (){
    // check to see if we should start prg
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        $uniqid = uniqid();
        $_SESSION['post'][$uniqid] = $_POST;
        $_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

        header("HTTP/1.1 303 See Other");
        $header = "Location: " . $_SERVER['REQUEST_URI'] . '?prg=1&uniqid=' . $uniqid;
        header($header);
        die;
    }

    if (!isset($_SESSION['REQUEST_URI'])){
        @$_SESSION['post'] = null;
    } else {
        if (isset($_GET['prg'])){
            $uniqid = $_GET['uniqid'];
            $_POST = @$_SESSION['post'][$uniqid];
        } else {
            @$_SESSION['REQUEST_URI'] = null;
        }
    }
}