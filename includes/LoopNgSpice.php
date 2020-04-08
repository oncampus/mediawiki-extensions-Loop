<?php
/**
 * @description 
 * @ingroup Extensions
 * @author Kevin Berg <kevin.berg@th-luebeck.de>, Dennis Krohn <dennis.krohn@th-luebeck.de>
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This file cannot be run standalone.\n" );
}

class LoopNgSpice {

	var $input = '';
	var $text = '';
	var $id = '';
	var $args = array ();
	var $buttonText = '';
	var $xml_error = '';
	private $enableSendButton = false;
	private $openVar = "{";
	private $closeVar = "}";
	
	// define the tag names
	private $tagAttributes = "tag_attributes";
	private $netlistTagName = "ngspice_netlist";
	private $plotlistTagName = "ngspice_plot";
	private $imageTagName = "ngspice_image";
	private $titleTagName = "ngspice_title";
	private $rawViewTagName = 'ngspice_show_raw';
	private $tableViewTagName = 'ngspice_show_table';
	private $varconfigTagName = "ngspice_varconfig";
	private $varconfigNameTagName = "var";
	private $varRangeMin = "min";
	private $varRangeMax = "max";
	private $varDefaultValue = "value";
	private $varPosX = "x";
	private $varPosY = "y";
	private $varFieldWidth = "width";
	private $varLabel = "label";
	private $varBgColor = "bgcolor";
	private $varTextColor = "textcolor";
	private $netlistClassCss;
	private $varList;
	private $varConfClass;
	private $varConfs;
	private $netlist;
	private $plotlist;
	private $img;
	private $img_size;
	private $title;
	private $netlistVars;
	private $plotVars;
	private $rawView;
	private $tableView;
    private $resultConfig = array();
    
	static function onParserSetup( Parser $parser ) {
		$parser->setHook( 'ngspice', 'LoopNgSpice::renderLoopNgSpice' ); 
		return true;
    }
    
    public static function renderLoopNgSpice($input, array $args, Parser $parser, PPFrame $frame) {
    
        global $wgLoopNgSpiceUrl, $wgOut, $wgDefaultUserOptions;
		$parser->getOutput()->updateCacheExpiry( null );

		$renderMode = $wgOut->getUser()->getOption( 'LoopRenderMode', $wgDefaultUserOptions['LoopRenderMode'], true );

        if ( empty( $wgLoopNgSpiceUrl ) || $renderMode == "offline" ) {
            return new LoopException( wfMessage ( "loopngspice-error-no-service" )->text() );
		}
		$loopNgSpice = new LoopNgSpice( $input,$args );
		$parser->getOutput()->addModules( 'loop.ngspice.js' );
		$parser->getOutput()->addModules( 'skins.loop-resizer.js' );
        return $loopNgSpice->render($parser);

    }

	function __construct( $input, $args ) {
		global $wge_LoopNgspice_default;
		
		$this->args = $args;
		$this->id = uniqid ();
		$this->input = $input;

		if (array_key_exists ( 'text', $args )) {
			
			if ($args ["text"] != '') {
				
				$this->text = $args ["text"];
			} else {
				
				$this->buttonText = wfMessage ( "loopngspice-label-send" )->text();
				$this->xml_error = wfMessage ( "loopngspice-error-xml" )->text();
			}
		} else {
			
			$this->buttonText = wfMessage ( "loopngspice-label-send" )->text();
			$this->xml_error = wfMessage ( "loopngspice-error-xml" )->text();
		}
		
		$this->netlistClassCss = "d-none";
		$this->varList = "d-none";
		$this->varConfClass = "d-none";
		
		// get plugin config from Loop input
		$tagArray = $this->tagsToArray ( "<pre>" . $this->input . "</pre>" );

		// get all var configs (range)
		$this->varConfs = $this->getVarconfigs ( $tagArray );
		                              
		if(count($tagArray) != 0){
			
			// Raw text and Table view (if ngspice outputs text as result)
			$rawView = "" . $tagArray [$this->getTagIndex ( $tagArray, $this->rawViewTagName )] ['tag_value'] . "";
			$tableView = "" . $tagArray [$this->getTagIndex ( $tagArray, $this->tableViewTagName )] ['tag_value'] . "";
			
			if( ! ( $rawView === 'true' || $rawView === 'false' ) ) {
				$rawView = true;
			}
			
			if( ! ( $tableView === 'true' || $tableView === 'false' ) ) {
				$tableView = true;
			}
			
			if( isset( $rawView )) {
				$this->rawView = $rawView;
			} else {
				$this->rawView = true;
			}
			
			if( isset( $tableView )) {
				$this->tableView = $tableView;
			} else {
				$this->tableView = true;
			}

			// NET AND PLOT (raw with vars)
			$this->netlist = "" . $tagArray [$this->getTagIndex ( $tagArray, $this->netlistTagName )] ['tag_value'] . "";
			@$this->plotlist = "" . $tagArray [$this->getTagIndex ( $tagArray, $this->plotlistTagName )] ['tag_value'] . ""; //supress warnings if no plotlist needed

			
			// IMAGE
			$this->img = trim ( $tagArray [$this->getTagIndex ( $tagArray, $this->imageTagName )] ['tag_value'] );
			
			$image_name = $this->img;
			$filetitle = Title::newFromText ( $image_name, NS_FILE );
			$file = wfLocalFile ( $filetitle );
			$this->img = $file->getFullUrl ();
			
			# if loop is https, loop will set image urls to //loop.oncampus.de instead of https://...
			if( strpos($this->img, 'http') === false && strpos($this->img, 'https') === false) {
				$this->img = 'https:'.$this->img;
			}
			
			$this->img_size = getimagesize ( $this->img );

			// TITLE
			if( $this->getTagIndex ( $tagArray, $this->titleTagName ) ){
				$this->title = $tagArray [$this->getTagIndex ( $tagArray, $this->titleTagName )] ['tag_value'];
			} else {
				$this->title = "";
			}
			
			$this->enableSendButton = true;
		
			
		// Handle XML Error!	
		} else {
			$this->netlist = "";
			$this->plotlist = "";
			$this->img = "";
			$this->img_size = array(700,0);
			$this->title = $this->xml_error;
		}
		
		
		$this->netlist = str_replace ( "\n", "<br>", $this->netlist );
		$this->plotlist = str_replace ( "\n", "<br>", $this->plotlist );
		
		// get the vars from the netlist and the plotlist
		$this->netlistVars = $this->getVars ( $this->netlist );
		$this->plotVars = $this->getVars ( $this->plotlist );
		
		// get the var names with brackets
		foreach ( $this->netlistVars as $key => $value ) {
			$this->netlistVars [$key] = $this->openVar . $value . $this->closeVar;
		}
		
		// get the var names with brackets
		foreach ( $this->plotVars as $key => $value ) {
			$this->plotVars [$key] = $this->openVar . $value . $this->closeVar;
		}
		
		$this->resultConfig = $this->getResultConfigAsArray($tagArray);
		
	} // function
	
	/**
	 * Transforms the result config to an Array
	 * @param $tagArray
	 */
	private function getResultConfigAsArray($tagArray){
		$ngspiceResults = array();
			
		foreach( $tagArray as $array ) {
			if( strpos( $array['tag_name'], 'ngspice_result_' ) !== false ) {
				
				$resultNumber = str_replace('ngspice_result_', '', $array['tag_name']);
				
				$ngspiceResults[$resultNumber] = 'position:absolute; white-space: nowrap; ';
				
				# transform attributes to CSS attributes
				foreach( $array['tag_attributes'] as $attribute => $value ) {
					
					if( $attribute === 'bgcolor') {
						$attribute = 'background-color';
					} else if( $attribute === 'textcolor' ) {
						$attribute = 'color';
					} else if( $attribute === 'x' ) {
						$attribute = 'left';
					} else if( $attribute === 'y' ) {
						$attribute = 'top';
					}
					
					if( is_numeric($value)){
						$value .= 'px';
					}
					
					$ngspiceResults[$resultNumber] .= $attribute.':'.$value.'; ';
				}
			}
		}

		return urlencode(serialize($ngspiceResults));
		
	}
	
	/**
	 * returns index of all openVar chars in an array.
	 * @param $netlist        	
	 */
	private function getIndexArray($netlist) {
		$len = strlen ( $netlist );
		$index = array ();
		
		for($i = 0; $i <= $len; $i ++) {
			$char = substr ( $netlist, $i, 1 );
			
			if ($char == $this->openVar)
				array_push ( $index, $i );
		}
		
		return $index;
	}
	
	/**
	 * returns the string between two other strings.
	 * @param $content        	
	 * @param $start        	
	 * @param $end        	
	 */
	private function getBetween($content, $start, $end) {
		$r = explode ( $start, $content );
		
		if (isset ( $r [1] )) {
			$r = explode ( $end, $r [1] );
			return $r [0];
		}
		
		return '';
	}
	
	/**
	 * returns all names of vars in an array.
	 * @param $netlist        	
	 */
	private function getVars($netlist) {
		$vars = array ();
		
		foreach ( $this->getIndexArray ( $netlist ) as $a ) {
			array_push ( $vars, $this->getBetween ( substr ( $netlist, $a ), $this->openVar, $this->closeVar ) );
		}

		return $vars;
	}
	
	/**
	 * returns textfields for the vars.
	 * @param $vars
	 * @param string $placeholder        	
	 */
	private function getVarFields($vars, $varConfs) {
		$html = "";
		
		// conainer der verhindert, dass mehrere textfelder für variablen ausgegeben werde, die mehrfach vwendet werden
		$varGenerated = array();
		
		foreach ( $vars as $var ) {
			
			if( ! in_array($var,$varGenerated)) {
				$varGenerated[] = $var;
			} else {
				continue;
			}
			
			
			$conf = $this->searchVarConfsInArray ( $varConfs, $var );
			
			$style = "";
			$labelStyle = "";
			
			// ---------------------- L A B E L -----------------------------
			if (isset ( $conf ['label'] )) {
				if ($conf ['label'] != "") {
					$varName = $conf ['label'];
				} else {
					$varName = $var;
				}
			} else {
				$varName = $var;
			}
			
			// ---------------------- L A B E L -----------------------------
			
			// ---------------------- X -----------------------------
			if (isset ( $conf ['x'] )) {
				if ($conf ['x'] != "") {
					$style .= "left: " . $conf ['x'] . "px;";
					$labelStyle .= "left: " . $conf ['x'] . "px;";
				}
			}
			// ---------------------- X -----------------------------
			
			// ---------------------- Y -----------------------------
			if (isset ( $conf ['y'] )) {
				if ($conf ['y'] != "") {
					$yLabel = $conf ['y'] - 20;
					$style .= "top: " . $conf ['y'] . "px;";
					$labelStyle .= "top: " . $yLabel . "px;";
				}
			}
			
			// ---------------------- Y -----------------------------
			
			// ---------------------- W I D T H -----------------------------
			if (isset ( $conf ['width'] )) {
				if ($conf ['width'] != "") {
					$style .= "width: " . $conf ['width'] . "px;";
					$labelStyle .= "width: " . $conf ['width'] . "px;";
				}
			}
			// ---------------------- W I D T H -----------------------------
			
			// ---------------------- V A L U E ------------------------------
			if (isset ( $conf ['value'] )) {
				if ($conf ['value'] != "") {
					$value = $conf ['value'];
				} else {
					$value = "";
				}
			} else {
				$value = "";
			}
			// ---------------------- V A L U E ------------------------------
			
			// ---------------------- B G C O L O R---------------------------
			if (isset ( $conf ['bgcolor'] )) {
				if ($conf ['bgcolor'] != "") {
					$style .= "background-color: " . $conf ['bgcolor'] . ";";
				} 
			}
			// ---------------------- B G C O L O R---------------------------
			
			// ---------------------- T E X T C O L O R ----------------------
			if (isset ( $conf ['textcolor'] )) {
				if ($conf ['textcolor'] != "") {
					$style .= "color: " . $conf ['textcolor'] . ";";
				}
			}
			// ---------------------- T E X T C O L O R ----------------------
			
			$varName = str_replace ( $this->openVar, "", str_replace ( $this->closeVar, "", $varName ) );
			$label_ID = $var . "_label";
			
			$vc = json_encode ( $this->varConfs );
			$vc = str_replace ( "\"", "'", $vc );
			
			$idAppend = uniqid();
			$html .= "<label style=\"$labelStyle\" for=\"$var$idAppend\" id=\"$label_ID\">$varName</label>";
			$html .= "<input class=\"ngspice_input_number ngspice_textfield position-absolute\" type=\"number\" onkeydown=\"if (event.keyCode == 13) { Content_ngspice.sendContent(&quot;$this->id&quot;,&quot;$this->netlist&quot;,&quot;$this->plotlist&quot;,&quot;" . $vc . "&quot;,&quot;$this->rawView&quot;,&quot;$this->tableView&quot;,&quot;$this->resultConfig&quot;); return false; }\" style=\"$style\" name=\"$var\" id=\"$var$idAppend\" value=\"$value\" step=\"0.01\"/>";
		}
		
		return $html;
	}
	
	
	/**
	 * 
	 * @param $varConfs        	
	 * @param $varName        	
	 */
	private function searchVarConfsInArray($varConfs, $varName) {
		foreach ( $varConfs as $var => $confArray ) {
			if ($this->openVar . $var . $this->closeVar == $varName) {
				return $confArray;
			}
		}
	}
	
	
	/**
	 * returns the html formular for the vars.
	 * @param $netlistVars
	 * @param $plotVars
	 * @param $varConfs
	 */
	private function getForm($netlistVars, $plotVars, $varConfs) {
		$html = $this->openForm ();
		
		// get textfields
		$html .= $this->getVarFields ( $netlistVars, $varConfs );
		$html .= $this->getVarFields ( $plotVars, $varConfs );
		
		// close form is in the render function
		
		return $html;
	}
	
	/**
	 * returns the id for html elements
	 * @param $suffix
	 */
	private function getId($suffix) {
		return $this->id . "_" . $suffix;
	}
	
	
	private function openForm() {
		$fid = $this->getId ( 'ngspiceForm' );
		return "<form id=\"$fid\" class='ngspiceForm'>";
	}
	
	
	private function closeForm() {
		$bid = $this->getId ( "send" );
		
		$vc = json_encode ( $this->varConfs );
		$vc = str_replace ( "\"", "'", $vc );
		
		$html = "";

		if($this->enableSendButton){
			$html .= "<input type='button' value=\"$this->buttonText\" id=\"$bid\" class='ngspice_send btn btn-sm mw-ui-button mw-ui-primary mw-ui-progressive float-left mb-1 mr-1' form='ngspiceForm' onclick=\"Content_ngspice.sendContent(&quot;$this->id&quot;,&quot;$this->netlist&quot;,&quot;$this->plotlist&quot;,&quot;" . $vc . "&quot;,&quot;$this->rawView&quot;,&quot;$this->tableView&quot;,&quot;$this->resultConfig&quot;)\">";
		}
		
		$html .= "</form>";
		
		return $html;
	}
	
	
	/**
	 * searchrs the index of a tagName in a $tagArray
	 * JUST USE THIS FUNCTION IF IT EXISTS TAGNAME ONE TIMES!!
	 * @param $tagArray
	 * @param $tagName 
	 * return $index
	 */
	private function getTagIndex($tagArray, $tagName) {
		foreach ( $tagArray as $index => $tag ) {
			if ($tag ['tag_name'] == $tagName) {
				return $index;
			}
		}
		return false;
	}
	
	
	/**
	 * Reads xml tags and returns the values and attributes as array.
	 * @param $tagString the xml tags as string
	 * @return $tagArray
	 */
	function tagsToArray($tagString) {
		try {
			@$xml = new SimpleXMLElement ( $tagString );
			$tagArray = array ();
			$i = 0;
			
			// for every tag in $tagString
			foreach ( $xml->children () as $child ) {
				
				// store the tag name
				$name = $child->getName ();
				$tagArray [$i] ["tag_name"] = $name;
				
				// search tag attributes if exists
				foreach ( $child->attributes () as $attr ) {
					$name = $attr->getName ();
					$value = $attr;
					$tagArray [$i] ["tag_attributes"] [$name] = ( string ) $attr;
				}
				
				// get the value of the tag if exists
				if ($child != "" && $child != " ")
					$tagArray [$i] ["tag_value"] = ( string ) $child;
				
				$i ++;
			}
			
			return $tagArray;
			
		} catch ( Exception $e ) {
			
			$tagArray = array();
			return $tagArray;
			
		}
	} // function tagsToArray
	
	
	/**
	 * reads and returns all var configs as array
	 * @param $tagArray 
	 * return $varconfigArray
	 */
	private function getVarconfigs($tagArray) {
		$varconf = array ();
		
		foreach ( $tagArray as $index => $tag ) {
			if ($tag ['tag_name'] == $this->varconfigTagName) {
				
				$tagAttribs = $tag [$this->tagAttributes];
				$varName = $tagAttribs [$this->varconfigNameTagName];
				
				if (isset ( $tagAttribs [$this->varRangeMin] )) {
					$varconf [$varName] [$this->varRangeMin] = $tagAttribs [$this->varRangeMin];
				}
				
				if (isset ( $tagAttribs [$this->varRangeMax] )) {
					$varconf [$varName] [$this->varRangeMax] = $tagAttribs [$this->varRangeMax];
				}
				
				if (isset ( $tagAttribs [$this->varDefaultValue] )) {
					$varconf [$varName] [$this->varDefaultValue] = $tagAttribs [$this->varDefaultValue];
				}
				
				if (isset ( $tagAttribs [$this->varPosX] )) {
					$varconf [$varName] [$this->varPosX] = $tagAttribs [$this->varPosX];
				}
				
				if (isset ( $tagAttribs [$this->varPosY] )) {
					$varconf [$varName] [$this->varPosY] = $tagAttribs [$this->varPosY];
				}
				
				if (isset ( $tagAttribs [$this->varFieldWidth] )) {
					$varconf [$varName] [$this->varFieldWidth] = $tagAttribs [$this->varFieldWidth];
				}
				
				if (isset ( $tagAttribs [$this->varLabel] )) {
					$varconf [$varName] [$this->varLabel] = $tagAttribs [$this->varLabel];
				}
				
				if (isset ( $tagAttribs [$this->varBgColor] )) {
					$varconf [$varName] [$this->varBgColor] = $tagAttribs [$this->varBgColor];
				}
				
				if (isset ( $tagAttribs [$this->varTextColor] )) {
					$varconf [$varName] [$this->varTextColor] = $tagAttribs [$this->varTextColor];
				}
			}
		}
		
		return $varconf;
	} // function getVarConfigs
	
	
	public function render($parser) {
		
		$width = $this->img_size [0] . "px";
		$height = $this->img_size [1] . "px";
		
// 		if( $width === 'px' && $height === 'px' ) {
// 			$width = '100%';
// 			$height = '100%';
// 		}
		
		// plugin style in loop
		$style = "background-image:url($this->img); width:$width; height:$height;";
		
		// html output
		$return = "";

		$nid =  $this->getId ( "netlist" );
		$pid =  $this->getId ( "plotlist" );
		$nvid = $this->getId ( "netlistVars" );
		$pvid = $this->getId ( "plotVars" );
		$vcid = $this->getId ( "varConfs" );
		$nnid = $this->getId ( "ngspiceNetlist" );
		$ntid = $this->getId ( "ngspiceTitle" );
		$niid = $this->getId ( "ngspiceImg" );
		
		// visible plugin content
		$return .= "<h2 id=\"$ntid\" class='ngspiceTitle'>$this->title</h2>";
		$return .= "<div id=\"$nnid\" style=\"$style\" class=\"scale-frame\" data-width=\"$width\" data-height=\"$height\" >";
		$return .= $this->getForm ( $this->netlistVars, $this->plotVars, $this->varConfs );
		$return .= "</div>";
		$return .= $this->closeForm ();
		
		// box for the result image
		$return .= "<div id=\"ngspiceImg\" class='ngspicImg'></div>";
		$return .= "<div id=\"ngspiceResult\" class='ngspiceResult'></div>";
	

		return $return;
	} // function render

}

class SpecialLoopNgSpice extends UnlistedSpecialPage {

	public function __construct() {
		parent::__construct ( 'LoopNgSpice' );
	}
	
	public function execute($sub) {
	
		global $IP, $wgLoopNgSpiceUrl;

		$out = $this->getOutput();
		$request = $this->getRequest();
		$user = $this->getUser();
		

		$out->disable();

		if ( empty( $wgLoopNgSpiceUrl ) ) {
			$out->addHtml( new LoopException( "loopngspice-error-no-service" ) );
			return;
        }
		
		$id = uniqid();

		$filter = FILTER_DEFAULT;
		$_GET['table'] = filter_input(INPUT_GET, 'tableView', FILTER_VALIDATE_BOOLEAN);
		$_GET['raw'] = filter_input(INPUT_GET, 'rawView', FILTER_VALIDATE_BOOLEAN);
		$_GET['id'] = filter_input(INPUT_GET, 'id', $filter);
		$_GET['netlist'] = filter_input(INPUT_GET, 'netlist', $filter);
		$_GET['plotlist'] = filter_input(INPUT_GET, 'plotlist', $filter);
		$_GET['resultConfig'] = filter_input(INPUT_GET, 'resultConfig', $filter);

		$id = $_GET['id'] ;

		if(isset($_GET['netlist'])){

			if(isset($_GET['plotlist'])){

				if ($_GET['netlist'] != ""){

					$tmpDir = "$IP/loop/tmp";
					if ( !is_dir( $tmpDir ) ) {
						mkdir( $tmpDir, 0774 );
					}
					
					$fp = fopen("$tmpDir/$id" . "_netlist", "w+");
					fwrite($fp, $_GET['netlist']);
					fclose($fp);
					
					$fp = fopen("$tmpDir/$id" . "_plotlist", "w+");
					fwrite($fp, $_GET['plotlist']);
					fclose($fp);

					$netlist = realpath("$tmpDir/$id" . "_netlist");
					$plotlist = realpath("$tmpDir/$id" . "_plotlist");

					$post = array('netlist'=>'@'.$netlist, 'plotlist'=>'@'.$plotlist, 'id'=>$id, 'table'=>$_GET['table'], 'raw'=>$_GET['raw'], 'resultConfig'=>$_GET['resultConfig']);

					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $wgLoopNgSpiceUrl);
					curl_setopt($ch, CURLOPT_POST,1);
					
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
					$result=curl_exec ($ch);
					curl_close ($ch);
					#dd($result, $post);
					
				} else {
					echo 'Error: Netlist is empty.';
					return;
				}

			} else {
				echo 'Error: no plotlist.';
				return;
			}
				
		} else {
			echo 'Error: No netlist.';
			return;
		}
    }
}