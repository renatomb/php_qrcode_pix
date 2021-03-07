<?php
/*
# Biblioteca de funções para geração da linha do Pix copia e cola
# cujo texto é utilizado para a geração do QRCode para recebimento
# de pagamentos através do Pix do Banco Central.
#
#
# Desenvolvido em 2020 por Renato Monteiro Batista - http://renato.ovh
#
# Este código pode ser copiado, modificado, redistribuído
# inclusive comercialmente desde que mantida a refereência ao autor.
*/


function montaPix($px){
   /*
   # Esta rotina monta o código do pix conforme o padrão EMV
   # Todas as linhas são compostas por [ID do campo][Tamanho do campo com dois dígitos][Conteúdo do campo]
   # Caso o campo possua filhos esta função age de maneira recursiva.
   #
   # Autor: Eng. Renato Monteiro Batista
   */
   $ret="";
   foreach ($px as $k => $v) {
     if (!is_array($v)) {
        if ($k == 54) { $v=number_format($v,2,'.',''); } // Formata o campo valor com 2 digitos.
        $ret.=c2($k).cpm($v).$v;
     }
     else {
       $conteudo=montaPix($v);
       $ret.=c2($k).cpm($conteudo).$conteudo;
     }
   }
   return $ret;
 }

function cpm($tx){
    /*
    # Esta função auxiliar retorna a quantidade de caracteres do texto $tx com dois dígitos.
    #
    # Autor: Renato Monteiro Batista
    */
    if (strlen($tx) > 99) {
      die("Tamanho máximo deve ser 99, inválido: $tx possui " . strlen($tx) . " caracteres.");
    }
    /*
    Não aprecio o uso de die no código, é um tanto deselegante pois envolve matar.
    Mas considerando que 99 realmente é o tamanho máximo aceitável, estou adotando-o.
    Mas aconselho que essa verificação seja feita em outras etapas do código.
    Caso não tenha entendido a problemática consulte  a página 4 do Manual de Padrões para Iniciação do Pix.
    Ou a issue 4 deste projeto: https://github.com/renatomb/php_qrcode_pix/issues/4
    */
    return c2(strlen($tx));
}
 
function c2($input){
    /*
    # Esta função auxiliar trata os casos onde o tamanho do campo for < 10 acrescentando o
    # dígito 0 a esquerda.
    #
    # Autor: Renato Monteiro Batista
    */
    return str_pad($input, 2, "0", STR_PAD_LEFT);
}


function crcChecksum($str) {
   /*
   # Esta função auxiliar calcula o CRC-16/CCITT-FALSE
   #
   # Autor: evilReiko (https://stackoverflow.com/users/134824/evilreiko)
   # Postada originalmente em: https://stackoverflow.com/questions/30035582/how-to-calculate-crc16-ccitt-in-php-hex
   */
  // The PHP version of the JS str.charCodeAt(i)
   function charCodeAt($str, $i) {
      return ord(substr($str, $i, 1));
   }

   $crc = 0xFFFF;
   $strlen = strlen($str);
   for($c = 0; $c < $strlen; $c++) {
      $crc ^= charCodeAt($str, $c) << 8;
      for($i = 0; $i < 8; $i++) {
            if($crc & 0x8000) {
               $crc = ($crc << 1) ^ 0x1021;
            } else {
               $crc = $crc << 1;
            }
      }
   }
   $hex = $crc & 0xFFFF;
   $hex = dechex($hex);
   $hex = strtoupper($hex);
   return $hex;
}

?>