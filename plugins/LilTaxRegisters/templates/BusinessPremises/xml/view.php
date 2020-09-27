<?php
    use Cake\Core\Configure;
    use Cake\Routing\Router;
    use Cake\Utility\Xml;
    use LilTaxRegisters\Lib\TaxRegistersXml;
	
    $bpArray = TaxRegistersXml::businessPremise($businessPremise);
    $envelope = TaxRegistersXml::envelope($bpArray);
	
	$XmlObject = Xml::fromArray($envelope, ['format' => 'tags', 'return' => 'domdocument', 'pretty' => true]);
	echo $XmlObject->saveXML();