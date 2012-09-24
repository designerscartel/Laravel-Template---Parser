<?php
/**
* Laravel Template Parser Class
*
* Author : Matt Tullett
* Based on the template class by Philip Sturgeon
* license : http://philsturgeon.co.uk/code/dbad-license
*/
class Parser 
{

    var $parser;

    public function __construct()
    {
       $this->parser = new Lex\Parser();
    }


    public function parse($file, $data)
    {
        return $this->_parse(file_get_contents($file), $data);
    }


    public function parse_str($string, $data)
    {
    	 return $this->_parse($string, $data);
    }


    public function callback($plugin, $attributes, $content)
    {
    	$data = array();
    	return $this->parser->parse($content, $data);
    }


    private function _parse($content, $data)
    {
    	$this->parser->cumulativeNoparse(true);
    	$content = $this->parser->parse($content, $data, array($this, 'callback'));
		$content = $this->parser->injectNoparse($content);
    	return $content;

    }

}

// END Parser class