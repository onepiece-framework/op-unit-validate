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

/** Validate
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

	/** ASCII
	 *
	 * @param string $sourse
	 * @return boolean
	 */
	static private function _Ascii($sourse)
	{
		//	...
		if( is_array($sourse) ){
			$sourse = join("\n", $sourse);
		}

		//	...
		if( preg_match("/[^\x09\x0a\x0d\x20-\x7E]/", $sourse) ){
			return true;
		}
	}

	/** EMail
	 *
	 * @param string $sourse
	 * @return boolean
	 */
	static private function _Email($sourse)
	{
		//	...
		if( is_string($sourse) === false ){
			return;
		}

		//	...
		if( strlen($sourse) === 0 ){
			return;
		}

		//	Do not allow alias names.
		if( strpos($sourse, '+') !== false ){
			return true;
		}

		//	...
		if(!strpos($sourse, '@') ){
			return true;
		}

		//	...
		list($addr, $host) = explode('@', $sourse);

		//	...
		if( preg_match('/[^-_a-z0-9\.]/i', $addr, $m) ){
			return true;
		}

		//	...
		if(!Env::isLocalhost() and !checkdnsrr($host,'MX')){
			return true;
		}
	}

	/** Phone
	 *
	 * @param string $sourse
	 * @return boolean
	 */
	static private function _Phone($sourse)
	{
		if( preg_match('/[^-0-9\.\+\ )]/i', $sourse, $m) ){
			return true;
		}
	}

	/** Required
	 *
	 * @param  string|array $sourse
	 * @return boolean
	 */
	static private function _Required($sourse)
	{
		if( is_array($sourse) ){
			if( count($sourse) === 1 ){
				return true;
			}
		}else{
			if( strlen($sourse) === 0 ){
				return true;
			}
		}
	}

	/** Sanitize
	 *
	 * @param  array $form   form configuration.
	 * @param  array $sourse request by internet.
	 * @param  array $errors
	 * @return array
	 */
	static function Sanitize($form, $sourses)
	{
		//	...
		$errors = [];

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

			//	...
			foreach( explode(',', $input['validate'].',') as $validate ){
				//	...
				$fail = null;

				//	...
				$validate = trim($validate);

				//	...
				if( $st = strpos($validate, '(') ){
					$en = strpos($validate, ')');
					$value    = substr($validate, $st +1, $en - $st -1);
					$validate = substr($validate,      0, $st);
				}

				//	...
				$sourse = ifset($sourses[$name], '');

				//	...
				switch( $validate ){
					case '':
						break;

					case 'required':
						$fail = self::_Required($sourses[$name]);
						break;

					case 'ascii':
					case 'english':
						$fail = self::_Ascii($sourse);
						break;

					case 'email':
						$fail = self::_Email($sourse);
						break;

					case 'phone':
						$fail = self::_Phone($sourse);
						break;

					case 'short':
						if( $len  = strlen($sourse) ){
							$fail = ( $len < $value) ? true: false;
						}
						break;

					case 'long':
						$fail = (strlen($sourse) > $value) ? true: false;
						break;

					case 'number':
						if( strlen($sourse) ){
							$fail = is_numeric($sourse) ? false: true;
						}
						break;

					case 'min':
						if( is_numeric($sourse) and $sourse < $value ){
							$fail = true;
						}
						break;

					case 'max':
						if( is_numeric($sourse) and $sourse > $value ){
							$fail = true;
						}
						break;

					case 'positive':
						if( is_numeric($sourse) and $sourse < 0 ){
							$fail = true;
						}
						break;

					case 'negative':
						if( is_numeric($sourse) and $sourse > 0 ){
							$fail = true;
						}
						break;

					default:
						Notice::Set("Does not support this validation. ($validate)");
				}

				//	...
				if( $fail ){
					$errors[$name][$validate] = true;
				}
			}
		}

		//	...
		return $errors;
	}
}