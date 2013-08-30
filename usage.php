<?
#test and show usage
include 'SimplestXmlParser.php';

$xml='<?xml version="1.0" ?>
		<x par1="val1"> 
		<y>1</y><!--<comment>
	1</comment>--><y par2="asdlaskd">2</y><z>3</z>	</x>';
$obj=SimplestXmlParser::parse($xml);
echo $obj;
