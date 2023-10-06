<?php



$content = "<p>Vamos ver uma coisa aqui</p><Ul><li>primeiro teste</li><li>segundo teste</li><li>qualquercoisa aqui</li></Ul></p><p>Deu certo!</p>\r\nMas... e aqui? Será que foi?<p>Pq aqui foi";



$tidy = new Tidy();

$tidy->parseString( $content, array( 'indent'         => true,
                                     'show-body-only' => true,
                                     'wrap'           => 'none',
                                     'output-xhtml'   => true
) );



$tidy->cleanRepair();

echo ($tidy->html());
