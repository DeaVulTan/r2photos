<?php
class ParseXML {
   var $rulesFile; var $rules = array();
   function ParseXML($rulesFile) { $this->rulesFile = $rulesFile; }
   function &ParseRulesXML() { $content = file_get_contents($this->rulesFile); $parser = xml_parser_create(); xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); xml_parse_into_struct($parser, $content, $arr1, $arr2); xml_parser_free($parser); $this->rules[0] = $arr1; $this->rules[1] = $arr2; return $this->rules; }
}
?>