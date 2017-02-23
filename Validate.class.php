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

	/** Parse config.
	 *
	 * @param  array $config
	 * @return array
	 */
	static function _ParseConfig($config)
	{
		//	...
		$validate = [];

		//	...
		foreach(explode(',',$config) as $temp){
			$key = trim($temp);
			$validate[$key] = true;
		}

		//	...
		return $validate;
	}

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
			$io = true;

			//	...
			$validate = ifset($input['validate'], []);

			//	required
			if( isset($validate['required']) ){
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

			//	...
			if( isset($sourse[$name]) ){
				$result[$name] = Escape($sourse[$name]);
			}
		}

		//	...
		return $result;
	}
}