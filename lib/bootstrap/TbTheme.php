<?php


class TbTheme {
	public static $paths = array();
	public static $themes = array();
	public static $buildLog = array();
	public $toBuild;

	public function __construct($toBuild=null){
		$this->initBuild();
		if (is_array($toBuild)){
			$this->toBuild = $toBuild;
		}
	}

	public function getBasePath(){
		if (class_exists('Yii', false)){		
			return Yii::app()->getBasePath();
		}
		else if(defined('APP_BASEPATH')) {
			return APP_BASEPATH;
		}	
		else {
			throw new Exception("I CAN'T FIGURE OUT WHERE BASEPATH is!!!");		
		}
	}
	public function initBuild(){
	$this->startTime = 	date("Ymd_His");
	 static::$paths['THEMES'] = realpath(__DIR__) ."/themes";
	

	static::$paths['build_log'] = realpath(dirname(__FILE__)) ."/build_log_" . $startTime . ".json";
	static::$paths['app_root'] = realpath($this->getBasePath() . "/..");
	static::$paths['themes_root'] = static::$paths['app_root'] . "/themes";
	static::$paths['backups'] =  static::$paths['app_root'] ."/protected/data/theme_backups";
	
	static::$themes = $this->getThemes();
	}

	function loadThemeConfig($path){
	if (is_dir($path)){
		$path .= "/theme.json"; 
	}
	$data = json_decode(file_get_contents($path));
	return $data;
	}

	public function getThemes(){
	$out = array();
	foreach(glob(static::$paths['THEMES']."/*") as $f){
			if (is_dir($f)){
				$tmp = array('name' => basename($f), 'path' => $f, $cfg => $this->loadThemeConfig($f));
			}
	}
	return $out;
    }

    public function saveBuildLog($andEcho=false){
	file_put_contents(static::$paths['build_log'], json_encode(static::$buildLog));
	if ($andEcho === true){

		echo print_r(static::$buildLog, true) . "\n";
	}
	}

	public function getPendingThemes(){
		if (isset($this->toBuild) && is_array($this->toBuild) && !empty($this->toBuidld)){
			$out = array();
			foreach(static::$themes as $theme){
				if (in_array($theme->name, $this->toBuild, true)){
					$out[] = $theme;
				}
			}
			return static::$themes = $out;
		}
		else {
			return static::$themes;
		}
	}
	public function run(){
		echo "\n Looks Like there are " . count(static::$themes) . " to Build..\n";
		foreach($this->getPendingThemes() as $cfg){
			$this->buildTheme($cfg);
		}
		echo "\n Done Building Themes!\n";
	}

	public function buildTheme($cfg){
	
	$fldr = uniqid();
	$buildDir = realpath(static::$paths['THEMES'] . "/..") . "/" . $fldr;
	$bLog = array();
	$d2 = uniqid();
	mkdir("./" . $d2, 0777, true);
	
	chdir($d2);
	$archive = "../tbsrc.tar.gz";
	$cmd = sprintf('tar- -zxf %s ', $archive);
	$bLog[] = array("msg" => "untarred " . basename($archive) , "ts" => date("Y-m-d H:i:s"));
	$this->saveBuildLog();
	rename('./src', $buildDir);
	$bLog[] = array('msg' => 'renamed ./src to ' . $buildDir , "ts" => date("Y-m-d H:i:s"));
	$this->saveBuildLog();
	chdir($buildDir)
	$sync = sprintf('/bin/cp -rf %s/%s/* %s', static::$paths['THEMES'], $cfg->slug, $buildDir);
	exec($sync,$arr);
	$bLog[] = array('msg' => 'recursive copied custom files to ' . $buildDir , "ts" => date("Y-m-d H:i:s"));
	$makeCmd = 'make bootstrap';

	exec($makeCmd);
	$bLog[] = array('msg' => 'Made Bootstrap ', "ts" => date("Y-m-d H:i:s"));
	$appRoot = static::$paths['app_root'];
	$outDir = static::$paths['themes_root'] . "/" . $cfg->name;
	$bacDir = static::$paths['backups'];

	if (file_exists($outDir) && is_dir($outDir)){
		$cdir = getcwd();
		chdir(dirname($outDir));
		$newPath = sprintf('%s_%s.tar.gz', $cfg->name, uniqid());
		$cmd = sprintf('tar -zcf %s %s', $newPath , $cfg->name);
		exec($cmd, $arr);
		rename($newPath, $bacDir . "/" . basename($newPath));

		chdir($cdir);

	}
	else {
		mkdir($outDir, 0777, true);
	}
	if (file_exists('./bootstrap') && is_dir('./bootstrap')){
		$res = true;
	rename('./bootstrap', $outDir);
	$bLog[] = array('msg' => 'Renamed ./bootstrap to ' . $outDir , "ts" => date("Y-m-d H:i:s"));
	$bLog[] = array('msg' => 'Done With Theme ' . $cfg->name , "ts" => date("Y-m-d H:i:s"));
	static::$buildLog[] = $bLog;

	$this->saveBuildLog(true);
	$res = true;
	}
	else {
		$res = false;
		$bLog[] = array('error' => "BOOTSTRAP DIR NOT FOUND", 'msg' => "Error Built Bootstrap Dir not Found","ts" => date("Y-m-d H:i:s"));
		static::$buildLog[] = $bLog;
		$this->saveBuildLog(true);
	}
	return $res;
  }
}
