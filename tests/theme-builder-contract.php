<?php
/** Renderer contract simulation ONLY, not a substitute for Elementor Pro conditions tests. */
$root=getenv('P10_WP_ROOT');if(!$root||!getenv('P10_DISPOSABLE_TEST'))exit('Disposable site required.');
$_SERVER['HTTP_HOST']='example.test';$_SERVER['REQUEST_URI']='/';$_SERVER['SERVER_PROTOCOL']='HTTP/1.1';$_SERVER['REQUEST_METHOD']='GET';
$GLOBALS['test_location_match']='';
function elementor_theme_do_location($location){if($location===$GLOBALS['test_location_match']){echo '<div>MOCK_MATCHED_LOCATION</div>';return true;}return false;}
require $root.'/wp-load.php';
function bcheck($ok,$label){if(!$ok)throw new RuntimeException('FAIL: '.$label);echo "PASS: $label\n";}
$id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Builder fallback fixture','post_content'=>'PHP_FALLBACK_CONTENT'));
$GLOBALS['wp_query']=new WP_Query(array('page_id'=>$id));$GLOBALS['wp_the_query']=$GLOBALS['wp_query'];
$template=apply_filters('template_include',get_template_directory().'/page.php');
ob_start();include $template;$fallback=ob_get_clean();
bcheck(str_contains($fallback,'PHP_FALLBACK_CONTENT')&&!str_contains($fallback,'MOCK_MATCHED_LOCATION'),'No renderer match falls back to original PHP page');
$GLOBALS['test_location_match']='single';
$GLOBALS['wp_query']=new WP_Query(array('page_id'=>$id));$GLOBALS['wp_the_query']=$GLOBALS['wp_query'];
ob_start();include $template;$matched=ob_get_clean();
bcheck(1===substr_count($matched,'MOCK_MATCHED_LOCATION')&&!str_contains($matched,'PHP_FALLBACK_CONTENT'),'Matched renderer replaces content exactly once');
bcheck(1>=preg_match_all('/<header\s+class="p10-header(?: |")/',$matched),'No duplicate header around matched content');
$GLOBALS['p10_elementor_locations']=array('404','single');
ob_start();include get_template_directory().'/page-templates/theme-builder.php';$native=ob_get_clean();
bcheck(str_contains($native,'MOCK_MATCHED_LOCATION'),'404 route can use native single location if dedicated 404 location is unsupported');
echo "THEME BUILDER CONTRACT SIMULATION FINISHED. Actual Pro display conditions were NOT tested.\n";
