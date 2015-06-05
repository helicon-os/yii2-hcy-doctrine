<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace helicon\hcy\doctrine\orm;

/**
 * Description of Exception
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class Exception extends \yii\base\Exception
{
    
  public static function newError($message, array $params = array(), $code = null, $previous = null)
  {
      if (count($params) > 0) {
            $p = [];
            foreach ((array) $params as $name => $value) {
                $p['{' . $name . '}'] = $value;
            }
            $message = strtr($message, $p);
      }
      
      return new Exception($message, $code, $previous);
  }
  
}
