<?php
/** Server-side checks with Elementor Free installed; does NOT assert browser editor operation. */
$root = getenv('P10_WP_ROOT');
if (!$root || !getenv('P10_DISPOSABLE_TEST')) { exit('Disposable site required.'); }
$_SERVER['HTTP_HOST']='example.test'; $_SERVER['REQUEST_URI']='/'; $_SERVER['SERVER_PROTOCOL']='HTTP/1.1'; $_SERVER['REQUEST_METHOD']='GET';
require $root.'/wp-load.php';
function echeck($ok,$label) { if (!$ok) { throw new RuntimeException('FAIL: '.$label); } echo "PASS: $label\n"; }
wp_set_current_user(1);
echeck(did_action('elementor/loaded'),'Elementor Free loaded');
$manager=\Elementor\Plugin::$instance->widgets_manager;
$types=$manager->get_widget_types();
foreach(array('p10-profiles','p10-facts','p10-gallery') as $name) { echeck(isset($types[$name]),'Registered real widget '.$name); $controls=$types[$name]->get_controls(); echeck(!empty($controls),'Widget controls registered '.$name); }
$id=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Elementor renderer fixture','post_content'=>''));
$doc=\Elementor\Plugin::$instance->documents->get($id);
$doc->set_is_built_with_elementor(true);
update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode(array(array('id'=>'test123','elType'=>'section','settings'=>array(),'elements'=>array(array('id'=>'test124','elType'=>'column','settings'=>array('_column_size'=>100),'elements'=>array(array('id'=>'test125','elType'=>'widget','widgetType'=>'heading','settings'=>array('title'=>'NATIVE_HEADING_FIXTURE'),'elements'=>array())))))))));
$GLOBALS['wp_query']=new WP_Query(array('page_id'=>$id)); $GLOBALS['wp_the_query']=$GLOBALS['wp_query'];
echeck(p10_elementor_document($id),'Saved Elementor document recognized without post_content');
$template=apply_filters('template_include',get_template_directory().'/page.php');
echeck(str_ends_with($template,'elementor-full-width.php'),'Elementor document selects unconstrained theme template');
ob_start();include $template;$html=ob_get_clean();
echeck(str_contains($html,'NATIVE_HEADING_FIXTURE'),'Native Elementor content renders on frontend');
echeck(1===preg_match_all('/<header\s+class="p10-header(?: |")/', $html) && 1===substr_count($html,'<footer class="p10-footer"'),'Only one classic header and footer');
echeck(!str_contains($html,'max-width:78ch'),'No prose width wrapper around Elementor content');
// New ordinary page must keep classic fallback.
$plain=wp_insert_post(array('post_type'=>'page','post_status'=>'publish','post_title'=>'Classic fallback','post_content'=>'CLASSIC_FALLBACK_FIXTURE'));
$GLOBALS['wp_query']=new WP_Query(array('page_id'=>$plain)); $GLOBALS['wp_the_query']=$GLOBALS['wp_query'];
echeck(!p10_elementor_document($plain),'Classic document not mistaken for Elementor');
echeck(str_ends_with(apply_filters('template_include',get_template_directory().'/page.php'),'page.php'),'Classic template remains when no document exists');
foreach(array('page','p10_profile','p10_review','p10_package') as $type) { echeck(post_type_supports($type,'elementor'),'Editor support declared for '.$type); }
$profile=p10_source_find('p10_profile',p10_source_key(p10_source_manifest()['profiles'][0]['source_id']));
$GLOBALS['wp_query']=new WP_Query(array('p'=>$profile,'post_type'=>'p10_profile')); $GLOBALS['wp_the_query']=$GLOBALS['wp_query']; $GLOBALS['wp_query']->the_post();
foreach(array('p10-profiles'=>'Brandy','p10-facts'=>'Age','p10-gallery'=>'<img') as $name=>$expected) {
 $instance=\Elementor\Plugin::$instance->elements_manager->create_element_instance(array('id'=>'fixture01','elType'=>'widget','widgetType'=>$name,'settings'=>array('limit'=>48)));
 ob_start();$instance->render_content();$dynamic=ob_get_clean();
 echeck(str_contains($dynamic,$expected),'Dynamic widget renders actual public data: '.$name);
 if($name==='p10-profiles') echeck(!str_contains($dynamic,'p10-reveal'),'Dynamic profile cards remain visible after editor refresh');
}
file_put_contents(dirname(__DIR__).'/.local/elementor-fixture.html',$html);
echo 'ELEMENTOR SERVER TESTS FINISHED. Source-only plugin lacks packaged JS/CSS/vendor assets; browser editor not verified.' . "\n";
