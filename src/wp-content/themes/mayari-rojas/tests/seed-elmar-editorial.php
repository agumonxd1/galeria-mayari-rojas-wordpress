<?php
defined( 'ABSPATH' ) || exit;
if ( 'cli' !== PHP_SAPI ) exit( "CLI only\n" );
$term = get_term_by( 'slug', 'elmar-rojas', 'gmr_artist' );
if ( ! $term ) exit( "Elmar term missing\n" );
$biography = '<p>Artista guatemalteco, arquitecto y figura esencial de la generacion de pintores centroamericanos de los anos sesenta. Estudio Bellas Artes en la Escuela Nacional de Artes Plasticas de Guatemala y complemento su formacion en Perugia, Paris y Madrid.</p><p>Fue profesor universitario en la Facultad de Arquitectura de la Universidad de San Carlos de Guatemala, fundador institucional y primer Ministro de Cultura del pais. Su practica enlazo pintura, arquitectura, pensamiento y una investigacion constante de la memoria mestiza guatemalteca.</p>';
$history = '<p>La obra de Elmar Rojas rescata relatos, objetos y tradiciones populares sin recurrir a una transcripcion literal del folclore. Campanas, piedras, filtros de barro, personajes ceremoniales y paisajes abiertos se convierten en signos de una cultura viva.</p><p>Su lenguaje pictorico desarrollo atmosferas de misterio y evocacion mediante veladuras, colores puros y composiciones donde dialogan la herencia europea y la indigena. Ese universo personal fue asociado con una expresion guatemalteca del realismo magico.</p>';
$chronology = '<ol><li><strong>1964–1968</strong><span>Etapa de pintura impasto y exploracion formal.</span></li><li><strong>1968–1972</strong><span>Cronica Social: denuncia y critica frente a la realidad politica.</span></li><li><strong>1975–1980</strong><span>Serie Desnudos Fragmentados.</span></li><li><strong>1980–1984</strong><span>Serie Personajes Conmemorativos.</span></li><li><strong>Desde 1984</strong><span>Desarrollo de la serie Espantapajaros y consolidacion de su imaginario maduro.</span></li><li><strong>2018</strong><span>Fallecimiento de Elmar Rojas; su obra continua como referente del arte guatemalteco.</span></li></ol>';
update_term_meta( $term->term_id, 'gmr_artist_biography', $biography );
update_term_meta( $term->term_id, 'gmr_artist_history', $history );
update_term_meta( $term->term_id, 'gmr_artist_chronology', $chronology );
update_term_meta( $term->term_id, 'gmr_artist_media_ids', '5213,5214' );
update_term_meta( $term->term_id, 'gmr_artist_document_ids', '6494' );
echo wp_json_encode( array( 'term_id' => $term->term_id, 'media' => 2, 'documents' => 1, 'awards' => 'pending verification' ) ) . "\n";
