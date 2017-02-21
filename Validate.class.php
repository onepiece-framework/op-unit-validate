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
	 * @param  array $configs
	 * @param  array $sourse
	 * @param  array $errors
	 * @return array
	 */
	static function Sanitize($configs, $sourse, &$errors)
	{
		//	...
		$result = [];

		//	...
		if( empty($configs) ){
			Notice::Set("Configuration was empty.");
			return $result;
		}

		//	...
		foreach($configs as $config){
			//	...
			if(!$name = ifset($config['name']) ){
				D($config);
				continue;
			}

			//	...
			if( empty($config['validate']) ){
				$result[$name] = ifset($sourse[$name]);
			}else{
				//	...
			}


			continue;


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
