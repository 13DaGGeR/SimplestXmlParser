<?
/**
 * SimplestXmlParser
 * usage: 
 *		$obj=SimplestXmlParser::parse($xml);
 *		var_dump($obj->name(),$obj->attributes(),$obj->children(),$obj->value());
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author 13dagger
 * @todo: optimization
 */
class SimplestXmlParser {
	const MAX_DEPTH=20;
	private function __construct(){}
	private function __clone(){}
	/**
	 * @param string $xml your xml string
	 * @return SimplestXmlObj object
	 */
	public static function parse($xml){
		$xml=self::stripHeader($xml);
		return self::parseTag($xml);
	}
	private static function &stripHeader(&$xml){
		$regs=[
			'!<\?xml version="[\d\.]+"\s*(|encoding="([^"]+)")\s*\?>!s',
			'/<!--.*?-->/s',
			'!<\?xml.*?\?>!s',
		];
		preg_match($regs[0],$xml,$m);
		if($m){
			if(!empty($m[2])){
				$xml2=iconv($m[2],'UTF-8',$xml);
				if($xml2) $xml=$xml2;
			}
			$xml=preg_replace($regs,'',$xml);
		}
		return $xml;
	}
	static private function parseTag(&$xml,$depth=0){
		if($depth==self::MAX_DEPTH){
			echo "Error: SimplestXmlParser::MAX_DEPTH reached\n";
			return null;
		}
		preg_match('!^\s*<([a-z_][^\s>]*)\s*(.*?)(/?)>!si',$xml,$m);
		if($m){#starts with open tag
			$tagName=$m[1];
			$attrs=self::parseAttributes($m[2]);
			$tag=new SimplestXmlObj($tagName, $attrs);
			if(!$m[3]){#not empty tag (not like "<tag />")
				$xml=mb_substr($xml,mb_strlen($m[0],'UTF-8'),null,'UTF-8');
				$hasChildren=0;
				while(preg_match('!<(.)!',$xml,$m2)){
					if(!in_array($m2[1],['/','!'])){
						$tag->addChild(self::parseTag($xml,$depth+1));
						$hasChildren=1;
					}else
						break;
				}
				if(!$hasChildren){
					$value=self::parseValue($xml,$tag->name());
					$tag->setValue($value);
				}
				$xml=preg_replace('!.*?</'.$tag->name().'>!s','',$xml,1);
			}else{
				$xml=preg_replace('!\s*<.*?/>!s','',$xml,1);
			}
		}else{
			var_dump($xml);die;
		}
		return $tag;
	}
	static private function &parseValue(&$xml,$tagName){
		if(preg_match('/^\s*<!\[CDATA\[(.*?)\]\]>/s',$xml,$m))
			$value=$m[1];
		else{
			preg_match("!(.*?)</$tagName>!",$xml,$m);
			$repl=['&lt;'=>'<','&gt;'=>'>',
				'&amp;'=>'&','&apos;'=>"'",
				'&quot;'=>'"'];
			$value=strtr($m[1],$repl);
		}
		$xml=mb_substr($xml,mb_strlen($value,'UTF-8'),null,'UTF-8');
		return $value;
	}

	static private function &parseAttributes($str){
		$params=[];
		preg_match_all('!(\S+)\s*=\s*"([^"]*)"!',$str,$m);
		foreach($m[1] as $k=>$key){
			$value=$m[2][$k];
			$params[$key]=$value;
		}
		return $params;
	}
}
class SimplestXmlObj{
	private $_name='';#tag name
	private $_attr=[];#tag attributes
	private $_chi=[];#children
	private $_val='';#value
	public function __construct($name,$attr){
		$this->_name=$name;
		$this->_attr=$attr;
	}

	public function &children(){ return $this->_chi; }
	public function &attributes(){return $this->_attr;}
	public function &name(){return $this->_name;}
	public function &value(){return $this->_val;}
	public function __get($name){
		return isset($this->_attr[$name])?
			$this->_attr[$name]:
			null;
	}
	public function __toString(){
		$str='';
		$str.=$this->_name;
		if($this->_attr){
			$attr=[];
			foreach($this->_attr as $k=>$v)
				$attr[]="$k=>$v";
			$str.='('.implode(',',$attr).')';
		}
		$str.=':';
		if($this->_val){
			$str.=$this->_val;
		}elseif($this->_chi){
			$str.="\n".preg_replace('!^(.)!m',"\t$1",implode("\n",$this->_chi));
		}
		return $str;
	}

	public function addChild(SimplestXmlObj $obj){
		$this->_chi[]=$obj;
	}
	public function setValue($str){
		$this->_val=(string)$str;
	}
}
