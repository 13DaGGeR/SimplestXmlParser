<?
#test and show usage
include 'SimplestXmlParser.php';

$xml=file_get_contents('http://www.w3schools.com/xml/cd_catalog.xml');
$obj=SimplestXmlParser::parse($xml);
$xmlized=$obj->xmlize();

$compare1=preg_replace(['/<\?xml.*?>/s','/<!.*?>/s','!\s!s'],'',$xml);
$compare2=preg_replace('!\s!s','',$xmlized);
if ($compare1 != $compare2)
	echo "Error! Please, send feedback at https://github.com/13DaGGeR/SimplestXmlParser/issues .\n";
else
	echo "It works!";
