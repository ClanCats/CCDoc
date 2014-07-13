<?php namespace CCDoc;
/**
 * Documentaion Controller
 ** 
 *
 * @package		ClanCatsFramework
 * @author		Mario Döring <mario@clancats.com>
 * @version		2.0
 * @copyright 	2010 - 2014 ClanCats GmbH
 *
 */
class DocController extends \CCViewController 
{	
	/**
	 * the index function 
	 *
	 * @return Response|void
	 */
	public function action_index( $file ) 
	{
		$md = "No contents";
		$conf = array();
		
		if ( file_exists( $file ) )
		{
			$md = \CCFile::read( $file );
		}
		
		// try to read the configuration form the markdown file
		if ( substr( $md, 0, 1 ) == '{' )
		{
			$conf = \CCJson::decode( substr( $md, 0, strpos( $md, '}' )+1 ) );
			if ( !is_array( $conf ) )
			{
				$conf = array();
			}
			
			$md = substr( $md, strpos( $md, '}' )+1 );
		}
		
		// try to parse the topic from the file
		$file_name = basename( $file );
		if ( strpos( $file_name, '_' ) !== false )
		{
			$file_name = substr( $file_name, strpos( $file_name, '_' )+1 );
		}
		$file_name = \CCStr::replace( \CCStr::cut( $file_name, '.md' ), array( '_' => ' ' ) );
		
		
		// is there a redirect command?
		if ( isset( $conf['redirect'] ) )
		{
			return \CCRedirect::to( $conf['redirect'] );
		}
		
		$this->theme->topic = \CCArr::get( 'topic', $conf, $file_name );
		
		$this->theme->sidebar = \CCView::create( 'CCDoc::sidebar' );
		
		// generate the html
		$html = \Parsedown::instance()->parse( $md );
		
		// identifiy headers 
		preg_match_all("~<(h2|h3)*([^>]+)>(.+?)</(h2|h3)>~i", $html, $matches );
		
		$headers = array();
		
		foreach( $matches[0] as $key => $heading ) 
		{	
			$id = \CCStr::clean_url( $matches[3][$key], '-' ).'-'.($key+1);
			
			$headers[$id] = $matches[3][$key];
			
			$html = str_replace( 
				$heading,
				'<'.$matches[4][$key].' id="'.$id.'">'.$matches[3][$key].'</'.$matches[4][$key].'>',
				$html 
			);
		}
		
		$header_navigation = "";
		
		foreach( $headers as $id => $header )
		{
			$header_navigation .= "<li><a href='#".$id."'>".$header."</a></li>";
		}
		
		// Run the evals
		preg_match_all( "/\{\[(.*?)\]\}/", $html, $matches );
		
		foreach( $matches[0] as $key => $replace_string )
		{
			$html = str_replace( $replace_string, eval( 'return '.$matches[1][$key].';' ), $html );
		}
		
		echo "<h1 style='padding-top: 0;'>".$this->theme->topic."</h1>"."<ul class='content-navigation'>".$header_navigation."</ul>".$html;
	}
}