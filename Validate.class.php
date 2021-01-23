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

/** namespace
 *
 * @created   2018-01-22
 */
namespace OP\UNIT;

/** Used class
 *
 */
use OP\OP_CORE;
use OP\OP_UNIT;
use OP\IF_UNIT;
use function OP\Encode;

/** Validate
 *
 * @created   2017-01-31
 * @version   1.0
 * @package   unit-validate
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Validate implements IF_UNIT
{
	/** trait
	 *
	 */
	use OP_CORE, OP_UNIT;

	/** EMail
	 *
	 * @param  string  $value
	 * @return boolean $failed
	 */
	static private function _Email($value)
	{
		//	Do not allow alias names.
		if( strpos($value, '+') !== false ){
			return '+';
		}

		//	...
		if(($pos = strpos($value, '@')) === false ){
			return '@';
		}

		//	...
		$addr = substr($value, 0, $pos);
		$host = substr($value, $pos + 1);

		//	...
		if( empty($addr) ){
			return true;
		}

		//	...
		$m = null;
		if( preg_match('/([^-\._0-9a-z]+)/i', $addr, $m) ){
			return $m[1];
		}

		//	...
		/*
		if( $host !== 'gmail.com' ){
			if(!checkdnsrr($host,'MX') ){
				return $host;
			}
		}
		*/

		//	...
		if(!gethostbynamel($host) ){
			return $host;
		}

		//	...
		return false;
	}

	/** Phone
	 *
	 * @param  string $source
	 * @return boolean
	 */
	static private function _Phone($value)
	{
		$m = null;
		if( preg_match('/[^-0-9\.\+\ )]/i', $value, $m) ){
			return true;
		}
	}

	/** Regular Expression
	 *
	 * @see		 https://msdn.microsoft.com/ja-jp/library/20bw873z.aspx
	 * @see		 https://fossies.org/linux/www/php-7.2.5.tar.xz/php-7.2.5/ext/mbstring/oniguruma/doc/UNICODE_PROPERTIES
	 * @param	 string $value
	 * @param	 string $which
	 * @return	 boolean|string $result
	 */
	static private function _RegExp($value, $which)
	{
		switch( $which ){
			case 'integer':
				$eval = '/([^-0-9]+)/';
				break;

			case 'ascii':
			case 'english':
				$eval = '/([^\x09\x0a\x0d\x20-\x7E]+)/';
				break;

			case 'alphabet':
				$eval = '/([^a-z]+)/i';
				break;

			case 'alphanumeric':
				$eval = '/([^0-9a-z]+)/i';
				break;

			case 'kana':
				$eval = '/([^\p{Hiragana}\p{Katakana}]+)/';
				break;

			case 'Han':
			case 'Kanji': // Kanji character
				$eval = '/([^\p{Han}]+)/';
				break;

			case 'hiragana': // hiragana only
				$eval = '/([^\p{Hiragana}]+)/';
				break;

			case 'katakana': // zenkaku katakana
				$eval = '/([^\p{Katakana}]+)/';
				break;

			case 'hankaku': // hankaku
				$eval = '/([^ｱ-ﾝ_0-9a-zA-Z]+)/';
				break;

			case 'zenkaku':
				$eval = '/([\x09\x0a\x0d\x20-\x7E]+)/';
				break;

			case 'cjkv': // China, Japan, Korea, Vietnam
			case 'han':
			case 'chinese':
				$eval = '/([^\p{Han}]+)/';
				break;

			case 'chinese':
				$eval = '/([^\p{In_CJK_Unified_Ideographs}]+)/';
				break;

			default:
				$eval = $which;
			break;
		}

		//	...
		for( $i=0, $len=strrpos($eval, '/'); $i<$len; $i++ ){
			if( $i = strpos($eval, '/', $i) ){
				if( $i < $len and $eval[$i-1] !== '\\' ){
					Notice::Set("Escape error. ($eval)");
				//	$error[$key] = true;
					return false;
				}
			}
		}

		//	...
		$m = false;

		//	...
		if(!preg_match("{$eval}u", $value, $m) ){
			$m = false;
		}

		//	...
		return $m;
	}

	/** Required
	 *
	 * @param  string|array $source
	 * @return boolean
	 */
	static private function _Required($value)
	{
		//	...
		$error = true;

		//	...
		if( is_array($value) ){
			$value = join('', $value);
		}

		//	...
		if( is_string($value) and strlen($value) ){
			$error = false;
		}

		//	...
		return $error;
	}

	static function _ParseString($strings)
	{
		//	...
		$config = [];

		//	...
		foreach( explode(',', $strings) as $string ){
			if( $st  = strpos($string, '(') and
				$en  = strpos($string, ')') ){
				$val = substr($string, $st +1, $en - $st -1);
				$key = substr($string, 0, $st);
				$val = is_numeric($val) ? (int)$val: trim($val);
			}else{
				$key = $string;
				$val = true;
			}

			//	...
			$config[trim($key)] = $val;
		}

		//	...
		return $config;
	}

	/** Evaluations
	 *
	 * @param  array   $configs Validate configuration.
	 * @param  array   $values  Evalution value.
	 * @param  array   $errors  Errors
	 * @return boolean $io      True is successful.
	 */
	static function Evaluations($configs, $values, &$errors)
	{
		//	...
		$failed = null;

		//	...
		if( is_string($configs) ){
			if( file_exists($configs) ){
				$configs = include($configs);
			}else{
				Notice::Set("Has not been exists this file. ($configs)");
			}
		}

		//	...
		foreach( $configs as $key => $config ){
			if(!self::Evaluation($config, $values[$key] ?? null, $errors[$key], $values) ){
				$failed = true;
			}
		}

		//	...
		return $failed ? false: true;
	}

	/** Evaluate each value.
	 *
	 * @param  string  $rule
	 * @param  array   $value
	 * @param  array   $error
	 * @param  array   $values
	 * @return boolean $fail
	 */
	function Evaluation($rule, $value, &$error, $values=null)
	{
		//	...
		$rule  = Encode($rule);
		$value = Encode($value);

		//	...
		$failed = null;

		//	...
		if( is_string($rule) ){
			$rule = self::_ParseString($rule);
		}

		//	...
		foreach( $rule as $key => $eval ){
			switch( $key ){
				case '':
					break;

				case 'required':
					if( $error[$key] = self::_Required($value) ){
						$failed = true;
						break 2;
					}
					break;

				case 'number':
					if( $len = mb_strlen($value) ){
						$error[$key] = !is_numeric($value);
					}else{
						$error[$key] = false;
					}
					break;

				case 'integer':
				case 'ascii':
				case 'english':
				case 'alphabet':
				case 'alphanumeric':
				case 'han':
				case 'kana':
				case 'hiragana':
				case 'katakana':
				case 'hankaku':
				case 'zenkaku':
				case 'chinese':
					if( $regexp = self::_RegExp($value, $key) ){
						$regexp = $regexp[1];
					}
					$error[$key] = $regexp;
					break;

				case 'regex':
				case 'regexp':
					$error[$key] = self::_RegExp($value, $eval);
					break;

				case 'mail':
				case 'email':
				case 'mail-addr':
					$error[$key] = self::_Email($value);
					break;

				case 'phone':
					$error[$key] = self::_Phone($value);
					break;

				case 'short':
					$len = mb_strlen($value);
					if( $len === 0 ){
						$error[$key] = false;
					}else{
						$error[$key] = ($len < $eval) ? $eval - mb_strlen($value): false;
					}
					break;

				case 'long':
					$error[$key] = (mb_strlen($value) > $eval) ? mb_strlen($value) - $eval: false;
					break;

				case 'min':
				case 'max':
				case 'positive':
				case 'negative':
					if( strlen($value) === 0 ){
						$io = false;
					}else if( ! is_numeric($value) ){
						$io = true;
						$key= 'numeric';
					}else if( $key === 'min' ){
						$io = ($value < $eval) ? ($eval - $value): false;
					}else if( $key === 'max' ){
						$io = ($value > $eval) ? ($value - $eval): false;
					}else if( $key === 'positive' ){
						$io = $value <= 0 ? true: false;
					}else if( $key === 'negative' ){
						$io = $value >= 0 ? true: false;
					}
					$error[$key] = $io;
					break;

				case 'if':
					if(!$io = is_string($values[$eval]) ? $values[$eval]: join('', ifset($values[$eval], [])) ){
						break 2;
					}
					break;

				default:
					D("Has not been define this evalution. ($key)");
			}

			//	...
			if(!$failed and $error[$key] ){
				$failed = true;
			}
		}

		//	...
		return $failed ? false: true;
	}
}
