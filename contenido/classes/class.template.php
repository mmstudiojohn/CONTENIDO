<?php
/**
 * Project: 
 * CONTENIDO Content Management System
 * 
 * Description: 
 * CONTENIDO Template Engine
 * 
 * Requirements: 
 * @con_php_req 5.0
 * 
 *
 * @package    CONTENIDO Backend Classes
 * @version    1.2.3
 * @author     Jan Lengowski
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * @since      file available since CONTENIDO release <= 4.6
 * 
 * {@internal 
 *   created unknown
 *   modified 2008-06-30, Frederic Schneider, add security fix
 *   modified 2008-09-03  Timo Trautmann, DB Object is only created if necessary
 *   modified 2010-02-01  Ingo van Peeren, Removed deprecated functions and arrays because they are not used anymore 
 *	 modified 2011-06-14  if template dont exist in template path, serach for the template in module path.
 * 
 *   $Id$: 
 * }}
 * 
 */

if(!defined('CON_FRAMEWORK')) {
	die('Illegal call');
}

/**
 * class Template
 *
 * Light template mechanism
 *
 * @author Jan Lengowski <Jan.Lengowski@4fb.de>
 * @copyright four for business <http://www.4fb.de>
 * @author Stefan Jelner (Optimizations)
 * @version 1.0
 */
class Template
{

	/**
	 * Needles (static)
	 * @var array
	 */
	var $needles = array ();

	/**
	 * Replacements (static)
	 * @var array
	 */
	var $replacements = array ();

	/**
	 * Dyn_Needles (dynamic)
	 * @var array
	 */
	var $Dyn_needles = array ();

	/**
	 * Dyn_Replacements (dynamic)
	 * @var array
	 */
	var $Dyn_replacements = array ();

	/**
	 * Database instance
	 * @var object
	 */
	var $db;
	
	/**
	 * Dynamic counter
	 * @var int
	 */
	var $dyn_cnt = 0;

	/**
	 * Tags array (for dynamic blocks);
	 * @var array
	 */
	var $tags = array ('static' => '{%s}', 'start' => '<!-- BEGIN:BLOCK -->', 'end' => '<!-- END:BLOCK -->');

    /**
     * gettext domain (default: contenido)
     * @var string
     */
    var $_sDomain = "contenido";

	/**
	 * Constructor function
	 * @return void
	 */
	function Template($tags = false)
	{

		if (is_array($tags))
		{
			$this->tags = $tags;
		}
		
		$this->setEncoding("");
	} // end function

    /**
     * setDomain
     *
     * Sets the gettext domain to use for translations in a template
     *
	 * @param $sDomain	string	Sets the domain to use for template translations
     * @return none
     */    
    function setDomain ($sDomain)
    {
    	$this->_sDomain = $sDomain;
    }
    
	/**
	 * Set Templates placeholders and values
	 *
	 * With this method you can replace the placeholders
	 * in the static templates with dynamic data.
	 *
	 * @param $which String 's' for Static or else dynamic
	 * @param $needle String Placeholder
	 * @param $replacement String Replacement String
	 *
	 * @return void
	 */
	function set($which = 's', $needle, $replacement)
	{
		if ($which == 's')
		{ // static
			$this->needles[] = sprintf($this->tags['static'], $needle);
			$this->replacements[] = $replacement;

		} else
		{ // dynamic
			$this->Dyn_needles[$this->dyn_cnt][] = sprintf($this->tags['static'], $needle);
			$this->Dyn_replacements[$this->dyn_cnt][] = $replacement;

		}
	}

    /**
     * Sets an encoding for the template's head block.
     *
     * @param $encoding string Encoding to set
     */    
    function setEncoding ($encoding)
    {
    	$this->_encoding = $encoding;
    }
 	
	/**
	 * Iterate internal counter by one
	 *
	 * @return void
	 */
	function next()
	{
		$this->dyn_cnt++;
	}

	/**
	 * Reset template data
	 *
	 * @return void
	 */
	function reset()
	{
		$this->dyn_cnt = 0;
		$this->needles = array ();
		$this->replacements = array ();
		$this->Dyn_needles = array ();
		$this->Dyn_replacements = array ();
	}

	/**
	 * Generate the template and
	 * print/return it. (do translations sequentially to save memory!!!)
	 *
	 * @param $template string/file Template
	 * @param $return bool Return or print template
	 * @param $note bool Echo "Generated by ... " Comment
	 *
	 * @return string complete Template string
	 */
	function generate($template, $return = 0, $note = 0)
	{
	  
		global $cCurrentModule,$cfg,$lang,$client,$encode;
	
		Contenido_Vars::setVar('cfg', $cfg);
		Contenido_Vars::setVar('client', $client);
		Contenido_Vars::setVar('lang', $lang);
		Contenido_Vars::setVar('encoding', $encode);
		Contenido_Vars::setVar('fileEncoding', getEffectiveSetting('encoding', 'file_encoding','UTF-8'));
		
		$contenidoModuleHandler = new Contenido_Module_Handler($cCurrentModule);
       
		
		//check if the template is a file or a string
		if (!@ is_file($template))
		{
			#priority have module directory
		    if(is_file($contenidoModuleHandler->getTemplatePath($template))) {
		    	$content = $contenidoModuleHandler->getFilesContent('template','',$template);
		    }
		    else
			    $content = & $template; //template is a string (it is a reference to save memory!!!)
		}
		else
		{
		    
		    if(is_file($contenidoModuleHandler->getTemplatePath($template))) #priority have module directory
		    	$content = $contenidoModuleHandler->getFilesContent('template','',$template);    
		    else #file is in templates directory
			   $content = implode("", file($template)); //template is a file
		}

		$content = (($note) ? "<!-- Generated by CONTENIDO ".$cfg['version']."-->\n" : "").$content;

		$pieces = array();
		
		//replace i18n strings before replacing other placeholders 
		$this->replacei18n($content, "i18n"); 
		$this->replacei18n($content, "trans"); 

		//if content has dynamic blocks 
		if (preg_match("/^.*".preg_quote($this->tags['start'], "/").".*?".preg_quote($this->tags['end'], "/").".*$/s", $content)) { 
			//split everything into an array 
			preg_match_all("/^(.*)".preg_quote($this->tags['start'], "/")."(.*?)".preg_quote($this->tags['end'], "/")."(.*)$/s", $content, $pieces); 
			//safe memory 
			array_shift($pieces); 
			$content = ""; 
			//now combine pieces together 

			//start block 
			$content .= str_replace($this->needles, $this->replacements, $pieces[0][0]); 
			unset ($pieces[0][0]); 

			//generate dynamic blocks 
			for ($a = 0; $a < $this->dyn_cnt; $a ++) { 
				$content .= str_replace($this->Dyn_needles[$a], $this->Dyn_replacements[$a], $pieces[1][0]); 
			} 
			unset ($pieces[1][0]); 

			//end block 
			$content .= str_replace($this->needles, $this->replacements, $pieces[2][0]); 
			unset ($pieces[2][0]);
		} else { 
			$content = str_replace($this->needles, $this->replacements, $content); 
		}
		
		if ($this->_encoding != "")
		{
			$content = str_replace("</head>", '<meta http-equiv="Content-Type" content="text/html; charset='.$this->_encoding.'">'."\n".'</head>', $content);
		}
		  
		if ($return)
			return $content;
		else
			echo $content;
	} # end function
	/** 
    * replacei18n() 
    * 
    * Replaces a named function with the translated variant 
    * 
    * @param $template string Contents of the template to translate (it is reference to save memory!!!) 
    * @param $functionName string Name of the translation function (e.g. i18n) 
    */ 
	function replacei18n(& $template, $functionName) {

		$container = array();

		// Be sure that php code stays unchanged
		$php_matches = array();
		if (preg_match_all('/<\?(php)?((.)|(\s))*?\?>/i', $template, $php_matches)) {
			$x = 0;
			foreach ($php_matches[0] as $php_match) {
				$x++;
				$template = str_replace($php_match , "{PHP#".$x."#PHP}", $template);
				$container[$x] = $php_match;
			}
		}

		// If template contains functionName + parameter store all matches
		$matches = array();
		preg_match_all("/".preg_quote($functionName, "/")."\\(([\\\"\\'])(.*?)\\1\\)/s", $template, $matches);

		$matches = array_values(array_unique($matches[2]));
		for ($a = 0; $a < count($matches); $a ++) {
			$template = preg_replace("/".preg_quote($functionName, "/")."\\([\\\"\\']".preg_quote($matches[$a], "/")."[\\\"\\']\\)/s", i18n($matches[$a], $this->_sDomain), $template);
		}

		// Change back php placeholder
		if (is_array($container)) {
			foreach ($container as $x => $php_match) {
				$template = str_replace("{PHP#".$x."#PHP}" , $php_match, $template);
			}
		}
	}

} # end class
?>