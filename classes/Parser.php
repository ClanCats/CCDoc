<?php namespace CCDoc;
/**
 * Parser
 **
 * 
 * @package       CCDoc
 * @author        ClanCats GmbH <info@clancats.com>
 * @version       1.0.0
 * @copyright     2010 - 2014 ClanCats GmbH
 */

use UI\HTML; 

class Parser
{
	/**
	 * static class initialisation
	 * @return void
	 */
	public static function _init()
	{
		// Do stuff
	}
	
	public static function function_info( $function )
	{
		list( $class, $function ) = explode( '::', $function );
		
		$trace = new \Core\CCError_Trace( array(
			'class' => $class,
			'function' => $function
		));
		
		$info = $trace->reflection_function_info();
		
		$buffer = static::format_info_message( $info['message'] );
		
		$buffer .= '<div class="clearfix">';
		if ( array_key_exists( 'param', $info ) ) 
		{
			$buffer .= '<div style="width: 80%; float: left; border-right: 1px solid #ddd;">
				<h4>parameters</h4>	
				<table class="table table-striped table-bordered">
					<tr>
						<th>type</th>	
						<th>var</th>
						<th>comment</th>	
					</tr>';
					
			foreach( $info['param'] as $param )
			{
				$param = explode( '    ', $param ); 	
				
				$buffer .= '<tr>
								<td class="code main-color" >'.( isset( $param[0] ) ? $param[0] : '' ).'</td>	
								<td class="code">'.( isset( $param[1] ) ? $param[1] : '' ).'</td>	
								<td>'.( isset( $param[2] ) ? $param[2] : '' ).'</td>	
							</tr>';
				
			}
			
			$buffer .= '</table></div>';
			
			// return
			if ( array_key_exists( 'return', $info ) )
			{
				$buffer .= '<div style="width: 19%; float: left; text-align: center;">
					<h4>returns</h4>
					<p class="main-color code">'.$info['return'][0].'</p>
				</div>';
			}			
			
			$buffer .= '</div>';
		}
		
		
		
		return HTML::div( $buffer )->class( 'function-info-container' );
	}
	
	/**
	 * Converts the info message to html
	 *
	 * @param string 	$message
	 * @return string
	 */
	public static function format_info_message( $message )
	{
		ob_start();
		
		$in_code = false; 
		$lines = explode( "\n", $message ); 
		
		foreach( $lines as $index => $line )
		{
			// check if we are on the last line
			if ( array_key_exists( $index+1, $lines ) ) 
			{
				$line = $line."\n";
			}
			
			// check if a quote starts
			if ( strpos( $line, '<pre>' ) !== false ) 
			{
				$line = str_replace( '<pre>', '', $line ); $in_code = true;
				echo '<pre class="prettyprint code"><code class="language-php">';
			}
			// check if a quote ends 
			elseif ( strpos( $line, '</pre>' ) !== false ) 
			{
				$line = str_replace( '</pre>', '', $line ); $in_code = false;
				echo '</code></pre>';
			}
			
			// dont make breaks if we are in a quote
			if ( $in_code ) 
			{
				$line = htmlentities( $line );
			} else {
				$line = nl2br( $line );
			}
			
			// ouptu the line
			echo $line;
		}
		
		return ob_get_clean();
	}
}
