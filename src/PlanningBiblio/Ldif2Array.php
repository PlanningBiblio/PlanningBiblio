<?php

namespace App\PlanningBiblio;

/**
* Class to read LDIF data
* @author Jérôme Combes <jerome dot combes at biblibre dot com>
* @author tobozo <tobozo at phpsecure dot info>
* @contributor Vladimir Struchkov <great_boba at yahoo dot com>
* @contributor Wojciech Sznapka <wojciech at sznapka dot pl>
* @copyleft (l) 2023 BibLibre
* @copyleft (l) 2006-2009 tobozo
* @package Ldif2Array
* @version 1.4
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @date 2023-10-05
*/

class Ldif2Array {

    /**
    * stores file name
    * @type string
    */
    public $file;

    /**
    * store data
    * @type string
    */
    public $rawdata;

    /**
    * store entries
    * @type array
    */
    public $entries = array();

    /**
    * encoding
    * @type string
    */
    public $encoding;


    //== constructor ====================================================================
    function __construct(/*string*/$file='', /*bool*/$process=false, /*string*/$encoding='UTF-8') {
      $this->file = $file;
      $this->encoding = $encoding;
      if($process) {
        $this->makeArray();
      }
    }


    /**
    * Convert string to UTF-8
    * @return string
    */
    private function convert(string $string): string|array|false
    {
        if ($this->encoding != 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $this->encoding);
        }

        return $string;
    }

    /**
    * returns the array of LDIF entries
    * @return array
    */
    function getArray() {
      return $this->entries;
    }


    /**
     * Sanity check before building the array, returns false if error
     */
    function makeArray(): bool {
       if ($this->file == '') {
           if($this->rawdata == '') {
             echo "No filename specified, aborting";
             return false;
           }
       } elseif (!file_exists($this->file)) {
           echo "File $this->file does not exist, aborting";
           return false;
       } else {
         $this->rawdata = file_get_contents($this->file);
       }

       if($this->rawdata == '') {
         echo "No data in file, aborting";
         return false;
       }

       $this->parse2Array();
       return true;
    }


    /**
     * Build the array in two passes
     */
    function parse2Array(): void {
        /**
        * Thanks to Vladimir Struchkov <great_boba yahoo com> for providing the
        * code to extract base64 encoded values
        */

        $arr1 = explode("\n", str_replace("\r", '', $this->rawdata));
        $i=$j = 0;
        $arr2 = array();

        /* First pass, rawdata is splitted into raw blocks */
        foreach($arr1 as $v) {
            if (trim($v) === '') {
                ++$i;
                $j = 0;
            } else {
                $arr2[$i][$j++] = $v;
            }
        }

        /* Second pass, raw blocks are updated with their name/value pairs */
        foreach($arr2 as $k1 => $v1) {
            $i = 0;
            $decode = false;
            foreach($v1 as $v2) {
                if (str_contains($v2, '::')) { // base64 encoded, chunk start
                    $decode = true;
                    $arr = explode(':', str_replace('::', ':', $v2));
                    $i = $arr[0];
                    $this->entries[$k1][$i] = trim(base64_decode($arr[1]));
                } elseif (str_contains($v2, ':')) {
                    $decode = false;
                    $arr = explode(':', $v2);
                    $count = count($arr);
                    if ($count != 2) {
                        for($i = $count-1; $i>1; --$i)
                            $arr[$i-1] .= ':' . $arr[$i];
                    }
                    $i = $arr[0];

                    // handling arrays in ldap entry
                    if (isset($this->entries[$k1][$i])) {
                      if (!is_array($this->entries[$k1][$i])) {
                        $this->entries[$k1][$i] = array($this->entries[$k1][$i]);
                      }
                      $this->entries[$k1][$i][] = trim($this->convert($arr[1]));
                    } else {
                      $this->entries[$k1][$i] = trim($this->convert($arr[1]));
                    }
                } elseif ($decode) {
                    // base64 encoded, next chunk
                    $this->entries[$k1][$i] .= trim(base64_decode($v2));
                } else {
                    $this->entries[$k1][$i] = trim($this->convert($v2));
                }
            }
        }
    }



}; // end class 
