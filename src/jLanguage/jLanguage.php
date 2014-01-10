<?php
/**
 * @author: Joan Gabriel Peralta Santana <jgdev> - http://github.com/jgdev ;
 * @version: 1.0;
 * 
 * jLanguage - Create a system for web with multi language support;
 *
 */

class jLanguage
{
	/**
	 * @param $config - array : Configuration of the jLanguage, see 
     *                          http://github.com/jgdev/jLanguage/readme.md section 'configuration' for
     *                          more information;
	 */
	private $config = array();

	/**
	 * @param $lang_select - array : Index of languages selected in data array;
	 */
	private $lang_select = array();

	/**
	 * @param $lang_data - array : Data of json files loaded;
	 */
	private $lang_data = array();

	/**
	 * @param $lang_data - boolean : if data has been loaded;
	 */
	private $data_loaded = false;

	/**
	 * Function __construct() : will be start when a new instance is created;
	 *
	 * @param $config - array : Configuration of the jLanguage, see 
     *                          http://github.com/jgdev/jLanguage/readme.md section 'configuration' for
     *                          more information;
	 */
	public function __construct($config)
	{
		$autoconfig = array(
			'auto_shutdown' => true, 
			'auto_load' => true,
			'lang_default' => null, 
			'auto_location' => true,
			'base_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data-lang'
		);
		
		$this->config = array_merge($autoconfig, $config);

		if(is_null($this->config['lang_default']))
			die("Please, configure the <b>lang_default</b> in jLanguage configuration.");

		// Load shutdown function.
		if($this->config['auto_load'])
			register_shutdown_function(array($this, "shutdown"));

		ob_start();
	}

	/**
	 * Function load_json_file() : Load a json file and convert to array data;
	 *
	 * @param $file - string : Filename to load;
	 * @return array;
	 *
	 */
	private function load_json_file($file)
	{
		if(file_exists($file))
		{
			$data = @file_get_contents($file);
		    $data = preg_replace('/\/\*(.*?)\*\//is', '', $data);
		    $data = json_decode($data, true);

		    $error = json_last_error();

		    if(empty($error))
		    	return $data;
		}

		return null;
	}

	/**
	 * Function load() : Load the data from file language selected;
	 *
	 */
	public function load()
	{
		if($this->data_loaded)
			return;

		if(!is_dir($this->config['base_dir']))
			die('Cannot find dir: <b>' . $this->config['base_dir'] . '</b>');

		$found = false;
		$data = array();

		if($this->config['auto_location'] && file_exists($this->config['base_dir'] . DIRECTORY_SEPARATOR . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . '.json'))
			$data = $this->load_json_file($this->config['base_dir'] . DIRECTORY_SEPARATOR . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . '.json');
		if($this->config['auto_location'] && file_exists($this->config['base_dir'] . DIRECTORY_SEPARATOR . @substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . '.json'))
			$data = $this->load_json_file($this->config['base_dir'] . DIRECTORY_SEPARATOR . @substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) . '.json');
		else if(file_exists($this->config['base_dir'] . DIRECTORY_SEPARATOR . $this->config['lang_default'] . '.json'))
			$data = $this->load_json_file($this->config['base_dir'] . DIRECTORY_SEPARATOR . $this->config['lang_default'] . '.json');

		if(empty($data) || is_null($data))
			die("Couldn't load the json file specified");
		else
			$this->lang_data = array_merge($data, $this->lang_data);

		$this->data_loaded = true;
	}

	/**
	 * Function set_section() : Indices selected to be used;
	 *
	 * @param $section - [array, string] : Section or sections to be load;
	 */
	public function set_section($section)
	{
		if(is_array($section))
		{
			foreach ($section as $key => $value)
			{
				if(!in_array($value, $this->lang_select))
					array_push($this->lang_select, $value);
			}
		}
		else
		{
			if(!in_array($section, $this->lang_select))
				array_push($this->lang_select, $section);
		}
	}

	/**
	 * Function pase() : Search and replace pieces of text that start
	 *                   with '[[' and end with ']]' that are within the data array;
	 *
	 * @param $htmldata - string : String to replace;
	 * @return string;
	 */
	private function parse($htmldata)
	{
		foreach ($this->lang_data as $key => $value) 
		{
			/*echo $key . ' => ';
			echo print_r($value);*/

			if(!$this->lang_data[$key])
				continue;

			if(is_array($value))
			{
				foreach ($value as $k => $v) 
				{
					$htmldata = str_replace('[[' . $k . ']]', $v, $htmldata);
				}
			}
			else
			{
				$htmldata = str_replace('[[' . $key . ']]', $value, $htmldata);
			}
		}

		return $htmldata;
	}

	/**
	 * Function put() : Insert or modify a value in a json file;
	 *
	 * @param 	$file 	- string : Filename;
	 * @param 	$key 	- string : Name of value;
	 * @param 	$value  - string : Value of value that are created or modified;
	 */
	public function put($file, $key, $value)
	{
		$file = $this->config['base_dir'] . DIRECTORY_SEPARATOR . $file . '.json';

		$data = $this->load_json_file($file);
		$array = array();

		if(is_array($key))
		{
			foreach ($key as $_k => $_v) 
			{
				$array[$_k] = array();
				$array[$_k][$_v] = $value;
				$data[$_k] = array_merge($data[$_k], $array[$_k]);
			}
		}
		else
		{
			$array[$key] = $value;
		}

		$data = json_encode($data);
		

		if(!$this->save($file, (!empty($data) ? $data : '')))
			die('Error saving file: ' . $file);
	}

	/**
	 * Function delete() : Remove a value in a json file;
	 *
	 * @param 	$file 	- string : Filename to edit;
	 * @param 	$key 	- string : Name of value to remove;
	 */
	public function delete($file, $key)
	{
		$file = $this->config['base_dir'] . DIRECTORY_SEPARATOR . $file . '.json';

		$data = $this->load_json_file($file);

		if(is_array($key))
		{
			foreach ($key as $_k => $_v) 
			{
				unset($data[$_k][$_v]);
			}
		}
		else
		{
			unset($data[$key]);
		}

		$data = json_encode($data);
		
		if(!$this->save($file, (!empty($data) ? $data : '')))
			die('Error saving file: ' . $file);
	}

	/**
	 * Function save() : Save changes in a json file;
	 *
	 * @param 	$file 	- string : Filename to save;
	 * @param 	$data 	- array : Data to save;
	 * @return  bool;
	 */
	private function save($file, $data)
	{
		try
		{
			$file = fopen($file, 'w');
			fwrite($file, $data);
			fclose($file);

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Function shutdown() : Change the contents of the page with the translated text;
	 *
	 */
	public function shutdown()
	{
		$html = ob_get_contents();
		ob_clean();

		// load data
		if($this->config['auto_load'])
			$this->load();

		//print_r($this->lang_data);

		echo $this->parse($html);
	}

	/**
	 * Function return_shutdown() : In case that the shutdown function it's not automatically, you can call this function to get
	 *							    the translated text;
	 *
	 */
	public function return_shutdown()
	{
		$html = ob_get_contents();
		ob_clean();

		// load data
		if($this->config['auto_load'])
			$this->load();

		return $this->parse($html);
	}

	/**
	 * Function shutdown() : Get the extension of a file;
	 *
	 * @param $file_name - string : Filename;
	 * @return string;
	 */
	private function get_file_extension($file_name)
	{
		try
		{
			return end(explode('.', $file_name));
		}
		catch (Exception $e)
		{
			return null;
		}
	}
}