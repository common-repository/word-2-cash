<?php
/*
Plugin Name: Word 2 Cash
Plugin URI: http://msafi.com/wordpress-plugins/turn-keywords-into-links-with-word-2-cash/
Description: This plugin will turn your words into cash
Version: 0.9.2
Author: Mohammed Safi
Author URI: http://msafi.com
*/

add_action('admin_menu', 'w2c_menu');
function w2c_menu() {
  add_menu_page('Word 2 Cash', 'Word 2 Cash', 8, __FILE__, 'w2c_admin');
}

function w2c_admin()
{
	if ($_POST['w2c-submitted'])
	{
		update_option('w2c_definitions', $_POST['w2c-definitions']);		
	}
?>
<div class="wrap">

	<h2>Turn Your Words into Cash -- Right Here!</h2>
	<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST">
	
		<table class="form-table">
			<tr><td>Keyword, Link (Example: "diet pills, http://lose30poundsin2seconds.com" without the quotes)</td></tr>
			<tr valign="top">
				<td><textarea wrap="off" style="white-space:nowrap;" name="w2c-definitions" rows="15" cols="90"><?php echo get_option('w2c_definitions'); ?></textarea></td>
			</tr>			
		</table>
		
		<input type="hidden" name="w2c-submitted" value="true" />
		
		<p class="submit">
			<input type="submit" class="button-primary" value="Save Changes" />
		</p>
		
	</form>
	
</div>
<?php 
}

add_filter('the_content', 'w2c_content_filter');
function w2c_content_filter($content)
{
	$keyword_definitions = get_option('w2c_definitions');
	
	$content = " $content ";

	if (!empty($keyword_definitions))
	{		
		$kw_array = array();
		
		// thanks PK for the suggestion
		foreach (explode("\n", $keyword_definitions) as $definition) 
		{
			$chunks = array_map('trim', explode(",", $definition));
			
			$total_chuncks = count($chunks);
			
			if($total_chuncks > 2) 
			{
				$i = 0;
				$url = $chunks[$total_chuncks-1];
				
				while($i < $total_chuncks-1) 
				{
					if (!empty($chunks[$i])) $kw_array[$chunks[$i]] = $url;
					$i++;
				}
			} 
			else 
			{
				list($keyword, $url) = array_map('trim', explode(",", $definition, 2));
					
				if (!empty($keyword)) $kw_array[$keyword] = $url;
			}
		}
		
						
		foreach ($kw_array as $name=>$url) 
		{
			if (stripos($content, $name) !== false) 
			{
				$name= preg_quote($name, '/');
				
				$replace="<a rel=\"nofllow\" target=\"_blank\" title=\"$1\" href=\"$url\">$1</a>";
				$regexp=str_replace('$name', $name, '/(?!(?:[^<\[]+[>\]]|[^>\]]+<\/a>))\b($name)\b/imsU');	
				$newtext = preg_replace($regexp, $replace, $content);			
				if ($newtext!=$content) 
				{							
					$links++;
					$content=$newtext;
				}	
			}		
		}
	}
	
	return trim( $content );
}