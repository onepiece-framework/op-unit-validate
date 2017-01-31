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
	/**
	 * Use OP-CORE.
	 */
	use OP_CORE
	{
		//	...
	}

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

	static function Get($configs, $sourse, &$errors)
	{
		//	...
		$result = [];

		//	...
		foreach($configs as $config){
			//	...
			if( empty($config['validate']) ){
				continue;
			}

			//	...
			$name  = $config['name'];
			$value = ifset($sourse[$name]);

			//	...
			if(!mb_strlen($value)){
				$errors[$name]['required'] = true;
				continue;
			}

			//	...
			switch( $type = gettype($config['validate']) ){
				case 'array':
					$validate = $config['validate'];
					break;

				case 'string':
					$validate = self::_ParseConfig($config['validate']);
					break;

				default:
					continue;
			}

			//	...
			$failed = null;
			foreach($validate as $key => $evalu){
				switch($key){
					case 'integer':
					case 'numeric':
					case 'float':
						$function_name = 'is_'.$key;
						$failed = $function_name($value) ? false: true;
						break;
					default:
						D("Does not define this key. ($key)");
						$failed = true;
				}

				//	...
				if( $failed ){
					$errors[$name][$key] = true;
					continue;
				}
			}

			//	...
			$result[$name] = $value;
		}

		//	...
		return $result;
	}
}
