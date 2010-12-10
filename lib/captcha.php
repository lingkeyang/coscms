<?php

/**
 * File contains very simple captcha class
 *
 * @package    coslib
 */

/**
 * Class contains contains simple methods for doing captcha
 *
 * @package    coslib
 */
class captcha {
    // {{{ static pulbic function createCaptcha()
    /**
     * very simple captcha function doing a multiplication
     * @return  string  the catcha to be used in forms
     */
    static public function createCaptcha(){
        if (isset($_SESSION['cstr'])){
            return $_SESSION['cstr'];
        }
        $num_1 = mt_rand  ( 20  , 40  );
        $num_2 = mt_rand  ( 20  , 40  );
        $str = "$num_1 + $num_2 = ?";
        $res = $num_1 + $num_2;
        $_SESSION['cstr'] = $str;
        $_SESSION['ckey'] = md5($res);
        return $str;
    }

    // }}}
    // {{{ static public function checkCaptcha($res)

    /**
     * Method for checking if entered answer to captcha is correct
     *
     * @param   int  checks if the entered int in a captcha form
     * @return  int 1 on success and 0 on failure.
     */
    static public function checkCaptcha($res){
        if (isset($_SESSION['ckey']) && md5($res) == $_SESSION['ckey']){
            return 1;
        } else {
            return 0;
        }
    }
    // }}} 
}