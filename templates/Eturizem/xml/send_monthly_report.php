<?php
    use Cake\Core\Configure;
    use Cake\Routing\Router;
    use Cake\Utility\Xml;

	$transformed = ['knjigaGostovMesecnoPorocilo' => []];
    $transformed['knjigaGostovMesecnoPorocilo']['row'][0] = [
        '@idNO' => $counter->no,                         // identifikacijska številka nastanitvenega obrata
        '@leto' => (int)substr($this->getRequest()->getQuery('month'), 0, 4),                       // leto poročila
        '@mesec' => (int)substr($this->getRequest()->getQuery('month'), 5, 2),                     // mesec poročila (numerično 1 do 12)
        '@statusPor' => $this->getRequest()->getQuery('status'),                     // status poročila (glej tudi Šifrant <statusPor />)
        '@stDodatnihlezisc' => (int)$this->getRequest()->getQuery('additional'),     // število dodatnih (pomožnih ležišč), ki ni zajeto v število stalnih ležišč, ki so navedena v registru
        '@stProdanihNE' => (int)$this->getRequest()->getQuery('units'),              // število prodanih nastanitvenih enot
        '@odprtDni' => (int)$this->getRequest()->getQuery('workdays'),               // število dni v mesecu, ko je bil nastanitveni obrat odprt.
    ];

    $XmlObject = Xml::fromArray($transformed, ['format' => 'tags', 'return' => 'domdocument', 'pretty' => true]);

    if (!empty($addXSL)) {
        $style = $XmlObject->createProcessingInstruction(
            'xml-stylesheet',
            sprintf('type="text/xsl" href="%s"', Router::url('/eturizem_monthly.xsl', true))
        );
        $XmlObject->insertBefore($style, $XmlObject->firstChild);
    }

    if (!empty($addXmlHeader)) {
        echo $XmlObject->saveXML();
    } else {
        echo $XmlObject->saveXML($XmlObject->documentElement);
    }
