<?php
/**
 * unit-validate:/Validate.class.php
 *
 * @created   2017-01-31
 * @version   1.0
 * @package   unit-validate
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/**
 * Validate
 *
 * @created   2017-01-31
 * @version   1.0
 * @package   unit-validate
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Validate
{
	/** trait
	 *
	 */
	use OP_CORE;

	/** Sanitize
	 *
	 * @param  array $form   form configuration.
	 * @param  array $sourse request by internet.
	 * @param  array $errors
	 * @return array
	 */
	static function Sanitize($form, $sourse, &$errors)
	{
		//	...
		$result = [];

		//	...
		if( empty($form) ){
			Notice::Set("\$form is empty.");
			return $result;
		}

		//	...
		foreach($form['input'] as $name => $input){
			//	...
			if( empty($input['validate']) ){
				continue;
			}

			//	required
			if( ifset($input['validate']['required']) ){
				if( isset($sourse[$name]) ){
					if( is_array($sourse[$name]) ){
						if( count($sourse[$name]) === 1 ){
							$errors[$name]['required'] = true;
						}
					}else{
						if( strlen($sourse[$name]) === 0 ){
							$errors[$name]['required'] = true;
						}
					}
				}
			}

			//	string
			if( $str = ifset($input['validate']['string']) ){
				//	ASCII
				if( strpos($str, 'ascii') !== false ){
					if( preg_match("/[^\x09\x0a\x0d\x20-\x7E]/", $sourse[$name]) ){
						$errors[$name]['string'] = 'ascii';
					}
				}
			}

			//	...
			if( isset($sourse[$name]) ){
				$result[$name] = Escape($sourse[$name]);
			}
		}

		//	...
		return $result;
	}
}