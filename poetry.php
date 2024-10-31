<?php
/*
Plugin Name: Poetry
Description: Display a random poem with a widget or with a simple function call. Choose the poets you prefer.
Author: Julian Yanover
Version: 1.1
Author URI: http://mypoeticside.com
Plugin URI: http://mypoeticside.com/poetry-widget
*/

// MagPie Configuration

require_once(ABSPATH . WPINC . '/rss.php');
if (!defined('MAGPIE_FETCH_TIME_OUT')) {
	define('MAGPIE_FETCH_TIME_OUT', 2);
}
if (!defined('MAGPIE_USE_GZIP')) {
	define('MAGPIE_USE_GZIP', true);
}

function format_poem($data) {
	$poemxml = new MagpieRSS($data);

	// If the XML is parsed correctly
	if ($poemxml) {
		$printpoem = '';
		foreach($poemxml->items AS $value) {
			$printpoem .= '<ul class="random-poem"><li><strong>'. $value['title'].'</strong><br /><br />';
			$printpoem .= nl2br($value['description']);
			$printpoem .= '<br /><br /><small><em>Powered by <a href="http://mypoeticside.com" target="_blank">My poetic side</a></em></small>';
			$printpoem .= '</li></ul>';
		}

		return $printpoem;
	}
	else {
		$errormsg = 'The RSS could not be read.';

		if ($poemxml) {
			$errormsg .= ' (' . $poemxml->ERROR . ')';
		}

		return false;
	}
}

function obtain_poem($widgetInfo) {
	global $wp_version;

	$segment = $widgetInfo['poet23']."-".$widgetInfo['poet48']."-".$widgetInfo['poet65']."-".$widgetInfo['poet93']."-".$widgetInfo['poet131']."-".$widgetInfo['poet175']."-".$widgetInfo['poet181']."-".$widgetInfo['poet231']."-".$widgetInfo['poet244']."-".$widgetInfo['poet243']."-".$widgetInfo['poet259']."-".$widgetInfo['poet275']."-".$widgetInfo['poet299']."-".$widgetInfo['poet363']."-".$widgetInfo['poet412']."-".$widgetInfo['poet472']."-".$widgetInfo['poet482']."-".$widgetInfo['poet483']."-".$widgetInfo['poet500']."-".$widgetInfo['poet513']."-".$widgetInfo['poet518']."-".$widgetInfo['poet590']."-".$widgetInfo['poet623']."-".$widgetInfo['poet676']."-".$widgetInfo['poet761']."-".$widgetInfo['poet760']."-".$widgetInfo['poet839']."-".$widgetInfo['poet870']."-".$widgetInfo['poet912']."-".$widgetInfo['poet955']."-".$widgetInfo['poet967']."-".$widgetInfo['poet966']."-".$widgetInfo['poet1116']."-".$widgetInfo['poet1272']."-".$widgetInfo['poet1277']."-".$widgetInfo['poet1280']."-".$widgetInfo['poet1287']."-".$widgetInfo['poet1315']."-".$widgetInfo['poet1377']."-".$widgetInfo['poet1414']."-".$widgetInfo['poet1452']."-".$widgetInfo['poet1454']."-".$widgetInfo['poet1463']."-".$widgetInfo['poet1481']."-".$widgetInfo['poet1626']."-".$widgetInfo['poet1651']."-".$widgetInfo['poet1838']."-".$widgetInfo['poet1848']."-".$widgetInfo['poet1878']."-".$widgetInfo['poet1900'];
	
	if ($wp_version >= '2.7') {
		$client = wp_remote_get('http://mypoeticside.com/poetry-plugin.php?segment='.$segment);
	}
	else {
		$client = new Snoopy();
		$client->agent = MAGPIE_USER_AGENT;
		$client->read_timeout = MAGPIE_FETCH_TIME_OUT;
		$client->use_gzip = MAGPIE_USE_GZIP;

		@$client->fetch('http://mypoeticside.com/poetry-plugin.php?segment='.$segment);
	}

	return $client;
}

// This function shows the poem without widgets
function show_random_poem($title) {
	global $wp_version;
    $aux = '';
	$the_poem = '';
	//	We obtain the poem from http://mypoeticside.com
	$the_poem = obtain_poem($aux);

	if ($wp_version >= '2.7') {
		if ($the_poem['response']['code'] == 200) {
			$data = $the_poem['body'];
		}
	}
	else {
		if ($the_poem->status == '200') {
			$data = $the_poem->results;
		}
	}
    
    if ($title!="")
        echo '<strong>'.$title.'</strong><br />';
        
	echo format_poem($data);
    
    
}


function display_random_poem($widgetInfo) {
	global $wp_version;

	$the_poem = '';
	//	We obtain the poem from http://mypoeticside.com
	$the_poem = obtain_poem($widgetInfo);

	if ($wp_version >= '2.7') {
		if ($the_poem['response']['code'] == 200) {
			$data = $the_poem['body'];
		}
	}
	else {
		if ($the_poem->status == '200') {
			$data = $the_poem->results;
		}
	}

	return format_poem($data);
}

function widget_poems_and_poetry($args, $widget_args = 1)
{

	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_poems_and_poetry');
	if ( !isset($options[$number]) )
		return;

	$title = $options[$number]['title'];

	echo $before_widget;

	if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }

	//	Print the poem
	echo display_random_poem($options[$number]);

	echo $after_widget;

}

function widget_poems_and_poetry_control($widget_args)
{
	global $wp_registered_widgets;
	static $updated = false;

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	$options = get_option('widget_poems_and_poetry');
	if ( !is_array($options) )
		$options = array();

	if ( !$updated && !empty($_POST['sidebar']) )
	{
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id )
		{
			if ( 'widget_poems_and_poetry' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) )
			{
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				unset($options[$widget_number]);
			}
		}

		foreach ( (array) $_POST['widget-poems_and_poetry'] as $widget_number => $widget_value )
		{
			$title = isset($widget_value['title']) ? trim(stripslashes($widget_value['title'])) : '';

			$poet23 = isset($widget_value['poet-23']) ? '23' : '';
			$poet48 = isset($widget_value['poet-48']) ? '48' : '';
			$poet65 = isset($widget_value['poet-65']) ? '65' : '';
			$poet93 = isset($widget_value['poet-93']) ? '93' : '';
			$poet131 = isset($widget_value['poet-131']) ? '131' : '';
			$poet175 = isset($widget_value['poet-175']) ? '175' : '';
			$poet181 = isset($widget_value['poet-181']) ? '181' : '';
			$poet231 = isset($widget_value['poet-231']) ? '231' : '';
			$poet244 = isset($widget_value['poet-244']) ? '244' : '';
			$poet243 = isset($widget_value['poet-243']) ? '243' : '';
			$poet259 = isset($widget_value['poet-259']) ? '259' : '';
			$poet275 = isset($widget_value['poet-275']) ? '275' : '';
			$poet299 = isset($widget_value['poet-299']) ? '299' : '';
			$poet363 = isset($widget_value['poet-363']) ? '363' : '';
			$poet412 = isset($widget_value['poet-412']) ? '412' : '';
			$poet472 = isset($widget_value['poet-472']) ? '472' : '';
			$poet482 = isset($widget_value['poet-482']) ? '482' : '';
			$poet483 = isset($widget_value['poet-483']) ? '483' : '';
			$poet500 = isset($widget_value['poet-500']) ? '500' : '';
			$poet513 = isset($widget_value['poet-513']) ? '513' : '';
			$poet518 = isset($widget_value['poet-518']) ? '518' : '';
			$poet590 = isset($widget_value['poet-590']) ? '590' : '';
			$poet623 = isset($widget_value['poet-623']) ? '623' : '';
			$poet676 = isset($widget_value['poet-676']) ? '676' : '';
			$poet761 = isset($widget_value['poet-761']) ? '761' : '';
			$poet760 = isset($widget_value['poet-760']) ? '760' : '';
			$poet839 = isset($widget_value['poet-839']) ? '839' : '';
			$poet870 = isset($widget_value['poet-870']) ? '870' : '';
			$poet912 = isset($widget_value['poet-912']) ? '912' : '';
			$poet955 = isset($widget_value['poet-955']) ? '955' : '';
			$poet967 = isset($widget_value['poet-967']) ? '967' : '';
			$poet966 = isset($widget_value['poet-966']) ? '966' : '';
			$poet1116 = isset($widget_value['poet-1116']) ? '1116' : '';
			$poet1272 = isset($widget_value['poet-1272']) ? '1272' : '';
			$poet1277 = isset($widget_value['poet-1277']) ? '1277' : '';
			$poet1280 = isset($widget_value['poet-1280']) ? '1280' : '';
			$poet1287 = isset($widget_value['poet-1287']) ? '1287' : '';
			$poet1315 = isset($widget_value['poet-1315']) ? '1315' : '';
			$poet1377 = isset($widget_value['poet-1377']) ? '1377' : '';
			$poet1414 = isset($widget_value['poet-1414']) ? '1414' : '';
			$poet1452 = isset($widget_value['poet-1452']) ? '1452' : '';
			$poet1454 = isset($widget_value['poet-1454']) ? '1454' : '';
			$poet1463 = isset($widget_value['poet-1463']) ? '1463' : '';
			$poet1481 = isset($widget_value['poet-1481']) ? '1481' : '';
			$poet1626 = isset($widget_value['poet-1626']) ? '1626' : '';
			$poet1651 = isset($widget_value['poet-1651']) ? '1651' : '';
			$poet1838 = isset($widget_value['poet-1838']) ? '1838' : '';
			$poet1848 = isset($widget_value['poet-1848']) ? '1848' : '';
			$poet1878 = isset($widget_value['poet-1878']) ? '1878' : '';
			$poet1900 = isset($widget_value['poet-1900']) ? '1900' : '';

			$options[$widget_number] = compact( 'title', 'poet23', 'poet48', 'poet65', 'poet93', 'poet131', 'poet175', 'poet181', 'poet231', 'poet244', 'poet243', 'poet259', 'poet275', 'poet299', 'poet363', 'poet412', 'poet472', 'poet482', 'poet483', 'poet500', 'poet513', 'poet518', 'poet590', 'poet623', 'poet676', 'poet761', 'poet760', 'poet839', 'poet870', 'poet912', 'poet955', 'poet967', 'poet966', 'poet1116', 'poet1272', 'poet1277', 'poet1280', 'poet1287', 'poet1315', 'poet1377', 'poet1414', 'poet1452', 'poet1454', 'poet1463', 'poet1481', 'poet1626', 'poet1651', 'poet1838', 'poet1848', 'poet1878', 'poet1900' );
		}

		update_option('widget_poems_and_poetry', $options);
		$updated = true;
	}

	if ( -1 == $number )
	{
		$title = 'Random Poem';
		$number = '%i%';
		
		$poet23 = '23';
		$poet48 = '48';
		$poet65 = '65';
		$poet93 = '93';
		$poet131 = '131';
		$poet175 = '175';
		$poet181 = '181';
		$poet231 = '231';
		$poet244 = '244';
		$poet243 = '243';
		$poet259 = '259';
		$poet275 = '275';
		$poet299 = '299';
		$poet363 = '363';
		$poet412 = '412';
		$poet472 = '472';
		$poet482 = '482';
		$poet483 = '483';
		$poet500 = '500';
		$poet513 = '513';
		$poet518 = '518';
		$poet590 = '590';
		$poet623 = '623';
		$poet676 = '676';
		$poet761 = '761';
		$poet760 = '760';
		$poet839 = '839';
		$poet870 = '870';
		$poet912 = '912';
		$poet955 = '955';
		$poet967 = '967';
		$poet966 = '966';
		$poet1116 = '1116';
		$poet1272 = '1272';
		$poet1277 = '1277';
		$poet1280 = '1280';
		$poet1287 = '1287';
		$poet1315 = '1315';
		$poet1377 = '1377';
		$poet1414 = '1414';
		$poet1452 = '1452';
		$poet1454 = '1454';
		$poet1463 = '1463';
		$poet1481 = '1481';
		$poet1626 = '1626';
		$poet1651 = '1651';
		$poet1838 = '1838';
		$poet1848 = '1848';
		$poet1878 = '1878';
		$poet1900 = '1900';

	}
	else
	{
		$title = attribute_escape($options[$number]['title']);

		$poet23 = (bool) attribute_escape($options[$number]['poet23']);
		$poet48 = (bool) attribute_escape($options[$number]['poet48']);
		$poet65 = (bool) attribute_escape($options[$number]['poet65']);
		$poet93 = (bool) attribute_escape($options[$number]['poet93']);
		$poet131 = (bool) attribute_escape($options[$number]['poet131']);
		$poet175 = (bool) attribute_escape($options[$number]['poet175']);
		$poet181 = (bool) attribute_escape($options[$number]['poet181']);
		$poet231 = (bool) attribute_escape($options[$number]['poet231']);
		$poet244 = (bool) attribute_escape($options[$number]['poet244']);
		$poet243 = (bool) attribute_escape($options[$number]['poet243']);
		$poet259 = (bool) attribute_escape($options[$number]['poet259']);
		$poet275 = (bool) attribute_escape($options[$number]['poet275']);
		$poet299 = (bool) attribute_escape($options[$number]['poet299']);
		$poet363 = (bool) attribute_escape($options[$number]['poet363']);
		$poet412 = (bool) attribute_escape($options[$number]['poet412']);
		$poet472 = (bool) attribute_escape($options[$number]['poet472']);
		$poet482 = (bool) attribute_escape($options[$number]['poet482']);
		$poet483 = (bool) attribute_escape($options[$number]['poet483']);
		$poet500 = (bool) attribute_escape($options[$number]['poet500']);
		$poet513 = (bool) attribute_escape($options[$number]['poet513']);
		$poet518 = (bool) attribute_escape($options[$number]['poet518']);
		$poet590 = (bool) attribute_escape($options[$number]['poet590']);
		$poet623 = (bool) attribute_escape($options[$number]['poet623']);
		$poet676 = (bool) attribute_escape($options[$number]['poet676']);
		$poet761 = (bool) attribute_escape($options[$number]['poet761']);
		$poet760 = (bool) attribute_escape($options[$number]['poet760']);
		$poet839 = (bool) attribute_escape($options[$number]['poet839']);
		$poet870 = (bool) attribute_escape($options[$number]['poet870']);
		$poet912 = (bool) attribute_escape($options[$number]['poet912']);
		$poet955 = (bool) attribute_escape($options[$number]['poet955']);
		$poet967 = (bool) attribute_escape($options[$number]['poet967']);
		$poet966 = (bool) attribute_escape($options[$number]['poet966']);
		$poet1116 = (bool) attribute_escape($options[$number]['poet1116']);
		$poet1272 = (bool) attribute_escape($options[$number]['poet1272']);
		$poet1277 = (bool) attribute_escape($options[$number]['poet1277']);
		$poet1280 = (bool) attribute_escape($options[$number]['poet1280']);
		$poet1287 = (bool) attribute_escape($options[$number]['poet1287']);
		$poet1315 = (bool) attribute_escape($options[$number]['poet1315']);
		$poet1377 = (bool) attribute_escape($options[$number]['poet1377']);
		$poet1414 = (bool) attribute_escape($options[$number]['poet1414']);
		$poet1452 = (bool) attribute_escape($options[$number]['poet1452']);
		$poet1454 = (bool) attribute_escape($options[$number]['poet1454']);
		$poet1463 = (bool) attribute_escape($options[$number]['poet1463']);
		$poet1481 = (bool) attribute_escape($options[$number]['poet1481']);
		$poet1626 = (bool) attribute_escape($options[$number]['poet1626']);
		$poet1651 = (bool) attribute_escape($options[$number]['poet1651']);
		$poet1838 = (bool) attribute_escape($options[$number]['poet1838']);
		$poet1848 = (bool) attribute_escape($options[$number]['poet1848']);
		$poet1878 = (bool) attribute_escape($options[$number]['poet1878']);
		$poet1900 = (bool) attribute_escape($options[$number]['poet1900']);

	}
?>

		<p>
			<label for="poems_and_poetry-title-<?php echo $number; ?>">
				<?php _e( 'Title:' ); ?>
				<input class="widefat" id="poems_and_poetry-title-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
			</label>
		</p>

		<p>Choose the poets:</p>

		<p>
		<label for="poems_and_poetry-poet-23-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-23-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-23]"<?php checked( (bool) $poet23, true ); ?> /> Conrad Potter Aiken</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-48-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-48-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-48]"<?php checked( (bool) $poet48, true ); ?> /> Dante Alighieri</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-65-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-65-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-65]"<?php checked( (bool) $poet65, true ); ?> /> Maya Angelou</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-93-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-93-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-93]"<?php checked( (bool) $poet93, true ); ?> /> Wystan Hugh Auden</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-131-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-131-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-131]"<?php checked( (bool) $poet131, true ); ?> /> Charles Baudelaire</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-175-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-175-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-175]"<?php checked( (bool) $poet175, true ); ?> /> Elizabeth Bishop</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-181-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-181-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-181]"<?php checked( (bool) $poet181, true ); ?> /> William Blake</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-231-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-231-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-231]"<?php checked( (bool) $poet231, true ); ?> /> Emily Bronte</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-244-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-244-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-244]"<?php checked( (bool) $poet244, true ); ?> /> Elizabeth Barrett Browning</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-243-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-243-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-243]"<?php checked( (bool) $poet243, true ); ?> /> Robert Browning</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-259-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-259-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-259]"<?php checked( (bool) $poet259, true ); ?> /> Robert Burns</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-275-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-275-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-275]"<?php checked( (bool) $poet275, true ); ?> /> Lord Byron</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-299-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-299-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-299]"<?php checked( (bool) $poet299, true ); ?> /> Lewis Carroll</label>
		</p>

        <p>
		<label for="poems_and_poetry-poet-363-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-363-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-363]"<?php checked( (bool) $poet363, true ); ?> /> Samuel Taylor Coleridge</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-412-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-412-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-412]"<?php checked( (bool) $poet412, true ); ?> /> E.E. Cummings</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-472-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-472-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-472]"<?php checked( (bool) $poet472, true ); ?> /> Emily Dickinson</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-482-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-482-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-482]"<?php checked( (bool) $poet482, true ); ?> /> John Donne</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-483-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-483-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-483]"<?php checked( (bool) $poet483, true ); ?> /> Hilda Doolittle</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-500-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-500-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-500]"<?php checked( (bool) $poet500, true ); ?> /> Paul Laurence Dunbar</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-513-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-513-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-513]"<?php checked( (bool) $poet513, true ); ?> /> T.S. Eliot</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-518-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-518-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-518]"<?php checked( (bool) $poet518, true ); ?> /> Ralph Waldo Emerson</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-590-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-590-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-590]"<?php checked( (bool) $poet590, true ); ?> /> Robert Frost</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-623-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-623-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-623]"<?php checked( (bool) $poet623, true ); ?> /> Allen Ginsberg</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-676-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-676-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-676]"<?php checked( (bool) $poet676, true ); ?> /> Thomas Hardy</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-761-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-761-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-761]"<?php checked( (bool) $poet761, true ); ?> /> Langston Hughes</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-760-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-760-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-760]"<?php checked( (bool) $poet760, true ); ?> /> Ted Hughes</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-839-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-839-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-839]"<?php checked( (bool) $poet839, true ); ?> /> John Keats</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-870-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-870-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-870]"<?php checked( (bool) $poet870, true ); ?> /> Rudyard Kipling</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-912-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-912-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-912]"<?php checked( (bool) $poet912, true ); ?> /> D.H. Lawrence</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-955-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-955-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-955]"<?php checked( (bool) $poet955, true ); ?> /> Henry Wadsworth Longfellow</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-967-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-967-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-967]"<?php checked( (bool) $poet967, true ); ?> /> Amy Lowell</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-966-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-966-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-966]"<?php checked( (bool) $poet966, true ); ?> /> Robert Lowell</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1116-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1116-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1116]"<?php checked( (bool) $poet1116, true ); ?> /> Thomas Moore</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1272-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1272-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1272]"<?php checked( (bool) $poet1272, true ); ?> /> Sylvia Plath</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1277-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1277-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1277]"<?php checked( (bool) $poet1277, true ); ?> /> Edgar Allan Poe</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1280-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1280-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1280]"<?php checked( (bool) $poet1280, true ); ?> /> Alexander Pope</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1287-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1287-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1287]"<?php checked( (bool) $poet1287, true ); ?> /> Ezra Pound</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1315-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1315-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1315]"<?php checked( (bool) $poet1315, true ); ?> /> Sir Walter Raleigh</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1377-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1377-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1377]"<?php checked( (bool) $poet1377, true ); ?> /> Dante Gabriel Rossetti</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1414-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1414-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1414]"<?php checked( (bool) $poet1414, true ); ?> /> Carl Sandburg</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1452-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1452-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1452]"<?php checked( (bool) $poet1452, true ); ?> /> Anne Sexton</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1454-<?php echo $number; ?>"><input class="checkbox" type="checkbox" id="poems_and_poetry-poet-1454-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1454]"<?php checked( (bool) $poet1454, true ); ?> /> William Shakespeare</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1463-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1463-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1463]"<?php checked( (bool) $poet1463, true ); ?> /> Percy Bysshe Shelley</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1481-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1481-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1481]"<?php checked( (bool) $poet1481, true ); ?> /> Shel Silverstein</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1626-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1626-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1626]"<?php checked( (bool) $poet1626, true ); ?> /> Alfred Lord Tennyson</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1651-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1651-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1651]"<?php checked( (bool) $poet1651, true ); ?> /> Henry David Thoreau</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1838-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1838-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1838]"<?php checked( (bool) $poet1838, true ); ?> /> Walt Whitman</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1848-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1848-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1848]"<?php checked( (bool) $poet1848, true ); ?> /> Oscar Wilde</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1878-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1878-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1878]"<?php checked( (bool) $poet1878, true ); ?> /> William Wordsworth</label>
		</p>

		<p>
		<label for="poems_and_poetry-poet-1900-<?php echo $number; ?>"><input class="checkbox" type="checkbox"  id="poems_and_poetry-poet-1900-<?php echo $number; ?>" name="widget-poems_and_poetry[<?php echo $number; ?>][poet-1900]"<?php checked( (bool) $poet1900, true ); ?> /> William Butler Yeats</label>
		</p>


<?php
}

function widget_poems_and_poetry_register()
{

	// Check the API functions
	if ( !function_exists('wp_register_sidebar_widget') || !function_exists('wp_register_widget_control') )
		return;

	if ( !$options = get_option('widget_poems_and_poetry') )
		$options = array();
	$widget_ops = array('classname' => 'widget_poems_and_poetry', 'description' => __('Poetry Widget - Display random poems'));
	$control_ops = array('width' => 460, 'height' => 350, 'id_base' => 'poems_and_poetry');
	$name = __('Random poem');

	$id = false;
	foreach ( array_keys($options) as $o )
	{
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) )
			continue;
		$id = "poems_and_poetry-$o"; // Never never never translate an id
		wp_register_sidebar_widget($id, $name, 'widget_poems_and_poetry', $widget_ops, array( 'number' => $o ));
		wp_register_widget_control($id, $name, 'widget_poems_and_poetry_control', $control_ops, array( 'number' => $o ));
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$id )
	{
		wp_register_sidebar_widget( 'poems_and_poetry-1', $name, 'widget_poems_and_poetry', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'poems_and_poetry-1', $name, 'widget_poems_and_poetry_control', $control_ops, array( 'number' => -1 ) );
	}

}

add_action( 'widgets_init', 'widget_poems_and_poetry_register' );

?>