<?php
    use Cake\Core\Configure;
    use Cake\Routing\Router;
    use Cake\Utility\Xml;


	$transformed = array('knjigaGostov' => []);
    foreach ($registrations as $registration) {
        $transformed['knjigaGostov']['row'][] = [
            '@idNO' => $registration->counter->no,                                  // identifikacijska številka nastanitvenega obrata
            '@zst' => $registration->client_no,                                     // zaporedna številka gosta v knjigi gostov
            '@ime' => h($registration->name),                                       // ime gosta
            '@pri' => h($registration->surname),                                    // priimek gosta
            '@sp' => $registration->sex,                                            // spol
            '@dtRoj' => $registration->dob->format('Y-m-d'),                        // rojstni datum gosta
            '@drzava' => $registration->country_code,                               // koda države
            '@vrstaDok' => $registration->ident_kind,                               // vrsta gostovega identifikacijskega dokumenta
            '@idStDok' => h($registration->ident_no),                               // identifikacijska številka dokumenta
            '@casPrihoda' => $registration->start->format('Y-m-d\T00:00:00'),       // čas prihoda gosta (format: yyyy-MM-DDThh:mm:ss)
            '@casOdhoda' => $registration->end->format('Y-m-d\T00:00:00'),          // čas odhoda gosta (neobvezno)
            '@status' => $registration->kind == 'S' ? 0 : 1,                        // 1 = veljaven, 2 = storno
            '@ttObracun' => $registration->ttax_kind,                               // razlog oprostitve ali delnega plačila turistične takse
            '@ttVisina' => number_format($registration->ttax_amount, 2),            // višina polne turistične takse (EUR). Največ 4 decimalna mesta
        ];
    }

    $XmlObject = Xml::fromArray($transformed, ['format' => 'tags', 'return' => 'domdocument', 'pretty' => true]);

    if (!empty($addXSL)) {
        $style = $XmlObject->createProcessingInstruction(
            'xml-stylesheet',
            sprintf('type="text/xsl" href="%s"', Router::url('/eturizem.xsl', true))
        );
        $XmlObject->insertBefore($style, $XmlObject->firstChild);
    }

    if (!empty($addXmlHeader)) {
        echo $XmlObject->saveXML();
    } else {
        echo $XmlObject->saveXML($XmlObject->documentElement);
    }
