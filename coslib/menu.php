<?php

/**
 * @package     coslib
 */

/**
 * @package     coslib
 */


moduleLoader::includeModel ('content/article');

class menu {

    public static $options = array();

    public static function setOptions ($extra){
        self::$options = $extra;
    }

    /**
     * get system menu array from name
     * @param string    name of the system menu to get
     */
    public static function getSystemMenuArray ($name){
        $db = new db();
        $row = $db->selectOne('system_menu', 'name', $name);
        if (empty($row)) return array();
        $ary = unserialize($row['menu_array']);
        return $ary;
    }


    /**
     * update system menu array
     *
     * @param array  $values to insert into menu
     * @param string $name of menu to manipulate
     */
    public static function updateSystemMenuArray($menu, $name){

        $db = new db();
        $row = $db->selectOne('system_menu', 'name', $name);
        $menu = array('menu_array' => serialize($menu));
        if (empty($row)){
            $menu ['name'] = $name;
            $db->insert('system_menu' , $menu);
        } else {
            $db->update('system_menu', $menu, $row['id']);
        }
    }

    /**
     * method for attaching a new menu item to a system menu
     * Notice: Works on reference to the menu
     *
     * @param array $menu
     * @param <type> $val
     */
    public static function createSystemMenuItem(&$menu, $val){

        // create item to insert
        $item = array ();
        $item['id'] = $val['id'];
        $item['pid'] = 0;
        $item['title'] = html::specialEncode($val['title']);
        $menu[$item['id']] = $item;
    }

    /**
     *
     * @param   array   $ary tree array
     * @param   int     $id id to search for
     * @return  int     1 on success 0 on failure.
     */
    public static function menuItemHasChildren ($ary, $id){
        static $ret = 0;
        foreach ($ary as $key => $val){
            if ($val['id'] == $id){
                if (!empty($val['sub'])){
                    $ret = 1;
                } 
            }
            self::menuItemHasChildren($val['sub'], $id);
        }
        return $ret;
    }

    /**
     *
     * @param array     $menu
     * @param array     $values
     * @param int       $id
     */
    public static function deleteMenuItem(&$menu, $values, $id){
        foreach ($menu as $key => &$val) {
            if (empty($val)) continue;
            if ($val['id'] == $id){
                unset($menu[$key]);
            }
            if (isset($val['sub'])){
                self::deleteMenuItem($val['sub'], $values, $id);
            }
        }
    }

    /**
     *
     * @param array menu as array. Notice it works on the reference
     * @param array item to update
     * @param int   item id to update
     */
    public static function updateMenuItem(&$menu, $values, $id){
        foreach ($menu as $key => &$val) {
            if (empty($val)) continue;
            if ($val['id'] == $id){
                $val['title'] = html::specialEncode($values['title']);
            }
            if (isset($val['sub'])){
                self::updateMenuItem($val['sub'], $values, $id);
            }
        }
    }

    /**
     * recursive method for getting article tree.
     *
     * @param       int     id of article. Then we find parent_id and id of that ... etc
     * @return      array   str with html displaying the tree
     */
    public static function getTreeHTML($menu, $name, $id){
        static $stack, $first_done;

        if (!isset($stack)){
            $stack = self::getStack($name, $id);
            $stack = array_keys($stack);
        }

        static $str = '';

        if (!empty($menu)){
            if (isset(self::$options['first_ul'])  && !isset($first_done)){
                $str.= self::$options['first_ul'];
                $first_done = 1;
            } else {
                $str.="<ul class=\"content_tree\">\n";
            }
        }

        $element = array_shift($stack);
        foreach ($menu as $key => $val){
            $str.="<li>" .
            html::createLink(
                    contentArticle::getArticleUrl(
                            $val['id'], $val['title']),
                    $val['title']);

            if (!empty($val['sub'])){              
                if ( $element == $val['id']){
                    self::getTreeHTML($val['sub'], $name, $element);
                } else {
                    $str.="</li>\n";
                }
            } else {
                $str.="</li>\n";
            }
        }
        
        if (!empty($menu)) {
            $str.= "</ul>\n";
        }
        return $str;
    }



    /**
     *
     * @param string $name of menu to get
     * @return array|null   returns an array if any or null
     */
    public static function getMenu ($name){
        $db = new db();
        $row = $db->selectOne('system_menu', 'name', $name);
        if (empty($row)){
            return null;
        }
        return $row;
    }

    /**
     * recursive method for getting article tree.
     *
     * @param       int     id of article. Then we find parent_id and id of that ... etc
     * @return      array   str with html displaying the tree
     */
    public static function getManipTreeHTML($ary, $start = null){

        static $str = '';

        if ($start) {
            $str.= "<ol class=\"sortable\">\n";
        } else {
            $str.= "<ol>\n";
        }

        foreach ($ary as $key => $val){
            // no title - item has been deleted.
            if (empty($val['title'])) continue;
            $str.="<li id=\"list_$val[id]\"><div>";
            $str.= html::createLink(
                    contentArticle::getArticleUrl(
                            $val['id'], $val['title']),
                    $val['title']);
            $str.= "</div>";
            if (!empty($val['sub'])){
                self::getManipTreeHTML($val['sub']);
            } 
        }

        if (!empty($ary)) {
            $str.= "</li></ol>\n";
        }
        return $str;
    }

    /**
     * Modified code from:
     * 
     * http://old.nabble.com/Creating-Tree-Structure-from-associative-array-td6897320.html
     * 
     * @param  array $input_ary recieved from jquery sortable
     * @return array a menu array
     */

    public static function generateTreeFromAjax ($input_ary){
        $list = array();
        foreach ($input_ary as $key => $val){
            $art = new contentArticle();
            $art = $art->getArticleFromId($key);

            if ($val == 'root'){
                $list[] = array (
                    'id' => $key,
                    'pid' => 0,
                    'title' => html::specialEncode($art['title']));
            } else {
                $list[] = array (
                    'id' => $key,
                    'pid' => $val,
                    'title' => html::specialEncode($art['title']));
            }
        }

        //$list = $ary;
        $lookup = array();
        foreach( $list as $item ) {
            $item['sub'] = array();
            $lookup[$item['id']] = $item;
        }

        $tree = array();
        foreach( $lookup as $id => $foo ){
            $item = &$lookup[$id];
            if( $item['pid'] == 0 ){
                $tree[$id] = &$item;
            } else {
                if( isset( $lookup[$item['pid']] ) ) {
                    $lookup[$item['pid']]['sub'][$id] = &$item;
                } else {
                    $tree['_orphans_'][$id] = &$item;
                }
            }
        }

        return $tree;
    }

    /**
     *
     * @param   string $name of menu to get
     * @param   int    $id the id of element to get 'stack' to
     * @return  array  $stack
     */
    public static function getStack ($name, $id){
        $tree = menu::getSystemMenuArray($name);
        if (!$id) return array();
        $stack = get_parent_stack($id, $tree);
        if (!is_array($stack)) return array();
        $stack = array_flatten($stack, true);
        return $stack;
    }
}

/**
 * function for getting parent stack of array
 * found on php.net
 *
 * @param string $child
 * @param array  $stack
 * @return array parent stack
 */

function get_parent_stack($child, $stack) {
    foreach ($stack as $k => $v) {
        if (is_array($v)) {
            // If the current element of the array is an array, recurse it and capture the return
            $return = get_parent_stack($child, $v);

            // If the return is an array, stack it and return it
            if (is_array($return)) {
                return array($k => $return);
            }
        } else {
            // Since we are not on an array, compare directly
            if ($v == $child) {
                // And if we match, stack it and return it
                return array($k => $child);
            }
        }
    }

    // Return false since there was nothing found
    return false;
}

/**
 * function for flatting a array.
 * found on php.net
 *
 * @param array     $array to flatten
 * @param boolean   preserve keys or not
 * @param array     $r
 * @return array
 */
function array_flatten($array, $preserve = FALSE, $r = array()){

        foreach($array as $key => $value){
            if (is_array($value)){
                foreach($value as $k => $v){
                    if (is_array($v)) { $tmp = $v; unset($value[$k]); }
                }
                if ($preserve) $r[$key] = $value;
                else $r[] = $value;
            }
          // this is correct
          $r = isset($tmp) ? array_flatten($tmp, $preserve, $r) : $r;
        }
        // wrong spot:
        // $r = isset($tmp) ? array_flatten($tmp, $preserve, $r) : $r;
        return $r;
    }