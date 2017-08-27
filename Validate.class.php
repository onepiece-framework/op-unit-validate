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
	 * @param string $source
	 * @return boolean
	 */
	static private function _Ascii($source)
	{
		//	...
		if( is_array($source) ){
			$source = join("\n", $source);
		}

		//	...
		if( preg_match("/[^\x09\x0a\x0d\x20-\x7E]/", $source) ){
			return true;
		}
	}

	/** EMail
	 *
	 * @param string $source
	 * @return boolean
	 */
	static private function _Email($source)
	{
		//	...
		if( is_string($source) === false ){
			return;
		}

		//	...
		if( strlen($source) === 0 ){
			return;
		}

		//	Do not allow alias names.
		if( strpos($source, '+') !== false ){
			return '+';
		}

		//	...
		if(!strpos($source, '@') ){
			return '@';
		}

		//	...
		list($addr, $host) = explode('@', $source);

		//	...
		if(empty($host)){
			return 'host';
		}

		//	...
		if( $host !== 'gmail.com' ){
			//	...
			if( /* !Env::isLocalhost() and */ !checkdnsrr($host,'MX')){
				return $host;
			}
		}

		//	...
		if( preg_match('/[^-_a-z0-9\.]/i', $addr, $m) ){
			return $addr;
		}
	}

	/** Phone
	 *
	 * @param string $source
	 * @return boolean
	 */
	static private function _Phone($source)
	{
		if( preg_match('/[^-0-9\.\+\ )]/i', $source, $m) ){
			return true;
		}
	}

	/** Required
	 *
	 * @param  string|array $source
	 * @return boolean
	 */
	static private function _Required($source)
	{
		//	...
		if( empty($source) ){
			return true;
		}

		//	...
		if( is_array($source) ){
			if( count($source) === 1 ){
				return true;
			}
		}else if( is_string($source) ){
			if( strlen($source) === 0 ){
				return true;
			}
		}
	}

	/** Sanitize
	 *
	 * @param  array $form    form configuration.
	 * @param  array $sources request by internet.
	 * @param  array $errors
	 * @return array
	 */
	static function Validation($form, $sources, $saved)
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
			$source = ifset($sources[$name], ifset($saved[$name], ''));

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
				switch( $validate ){
					case '':
						break;

					case 'required':
						$fail = self::_Required($source);
						break;

					case 'ascii':
					case 'english':
						$fail = self::_Ascii($source);
						break;

					case 'email':
						$fail = self::_Email($source);
						break;

					case 'phone':
						$fail = self::_Phone($source);
						break;

					case 'short':
						$fail = (mb_strlen($source) < $value) ? true: false;
						break;

					case 'long':
						$fail = (mb_strlen($source) > $value) ? true: false;
						break;

					case 'number':
						if( strlen($source) ){
							$fail = is_numeric($source) ? false: true;
						}
						break;

					case 'min':
						if( is_numeric($source) and $source < $value ){
							$fail = true;
						}
						break;

					case 'max':
						if( is_numeric($source) and $source > $value ){
							$fail = true;
						}
						break;

					case 'positive':
						if( is_numeric($source) and $source < 0 ){
							$fail = true;
						}
						break;

					case 'negative':
						if( is_numeric($source) and $source > 0 ){
							$fail = true;
						}
						break;

					default:
						Notice::Set("Does not support this validation. ($validate)");
				}

				//	...
				if( $fail ){
					$errors[$name][$validate] = $fail;
				}
			}
		}

		//	...
		return $errors;
	}
}