<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace helicon\hcyii2\doctrine\orm;

/**
 * Description of Exception
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class Exception extends \yii\base\Exception
{
  
  public static function newGeneralConfigValidationError($aError, $validationConfig = [])
  {
    $validationConfig = array_merge([
      'description' => null,
      'types' => 'mixed',
      'required' => false
    ]);
    $aValueList = \array_merge_recursive($aValueList, [
      'idHint' => null,
      'id' => '?',
      'validation' => []
      ]
    );
    return new self('Doctrine Configuration error: '.
                    $aError.
                    \json_encode($aValueList));
  }
  
  public static function newConfigPropertyMissingError(array $values = [])
  {
    return new Exception (\Yii::t('yii2-hcy-doctrine', 'Doctrine configuration error: Configuration propery missing'));
  }
  
  public static function newConfigPropertyWrongTypeError($aConfigIdHint, $aId, $aFoundType, $aValidation)
  {
    return new Exception (\Yii::t('yii2-hcy-doctrine', 'Doctrine configuration error: Configuration propery missing'));
  }
  
}
