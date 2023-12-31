<?php
/*

Exemplo de um gerador de brcode e qrcode do pix para ser chamado via api.
As requisições são feitas com os parâmetros passados via GET.
O retorno é um json contendo:
   brcode: o brcode gerado
   imagem: o url para a imagem do qrcode gerado pelo google chart api

Live demo disponível em https://dinheiro.tech/qr-code-pix

(C) 2023 Renato Monteiro Batista - http://renato.ovh

Repositório do projeto completo no github: https://github.com/renatomb/php_qrcode_pix

*/


header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
if (isset($_GET["chave"]) && isset($_GET["beneficiario"]) && isset($_GET["cidade"])) {
   $chave_pix=strtolower($_GET["chave"]);
   $beneficiario_pix=$_GET["beneficiario"];
   $cidade_pix=$_GET["cidade"];
   if (isset($_GET["descricao"])){
      $descricao=$_GET["descricao"];
   }
   else { $descricao=''; }
   if ((!isset($_GET["identificador"])) || (empty($_GET["identificador"]))) {
      $identificador="***";
   }
   else {
      /*
      Atenção: Quando informado pelo recebedor, cada identificador deve ser único (ex.: UUID).
      Os identificadores são usados para a facilitar a conciliação da transação. Na auséncia do
      identificador recomendável o uso de três astericos: ***
      O identificador é limitado a 25 caracteres.
      */
      $identificador=$_GET["identificador"];
      if (strlen($identificador) > 25) {
         $identificador=substr($identificador,0,25);
      }
   }
   $gerar_qrcode=true;
}
else {
   $gerar_qrcode=false;
}

if (isset($_GET["valor"]) && (is_numeric($_GET["valor"]))){
   $valor_pix=preg_replace("/[^0-9.]/","",$_GET["valor"]);
}
else {
   $valor_pix="0.00";
}

if ($gerar_qrcode){
   include "funcoes_pix.php";
   $px[00]="01"; //Payload Format Indicator, Obrigatório, valor fixo: 01
   // Se o QR Code for para pagamento único (só puder ser utilizado uma vez), descomente a linha a seguir.
   //$px[01]="12"; //Se o valor 12 estiver presente, significa que o BR Code só pode ser utilizado uma vez. 
   $px[26][00]="br.gov.bcb.pix"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
   $px[26][01]=$chave_pix;
   if (!empty($descricao)) {
      /* 
      Não é possível que a chave pix e infoAdicionais cheguem simultaneamente a seus tamanhos máximos potenciais.
      Conforme página 15 do Anexo I - Padrões para Iniciação do PIX  versão 1.2.006.
      */
      $tam_max_descr=99-(4+4+4+14+strlen($chave_pix));
      if (strlen($descricao) > $tam_max_descr) {
         $descricao=substr($descricao,0,$tam_max_descr);
      }
      $px[26][02]=$descricao;
   }
   $px[52]="0000"; //Merchant Category Code “0000” ou MCC ISO18245
   $px[53]="986"; //Moeda, “986” = BRL: real brasileiro - ISO4217
   if ($valor_pix > 0) {
      // Na versão 1.2.006 do Anexo I - Padrões para Iniciação do PIX estabelece o campo valor (54) como um campo opcional.
      $px[54]=$valor_pix;
   }
   $px[58]="BR"; //“BR” – Código de país ISO3166-1 alpha 2
   $px[59]=$beneficiario_pix; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
   $px[60]=$cidade_pix; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
   $px[62][05]=$identificador;
//   $px[62][50][00]="BR.GOV.BCB.BRCODE"; //Payment system specific template - GUI
//   $px[62][50][01]="1.2.006"; //Payment system specific template - versão
   $pix=montaPix($px);
   $pix.="6304"; //Adiciona o campo do CRC no fim da linha do pix.
   $pix.=crcChecksum($pix); //Calcula o checksum CRC16 e acrescenta ao final.
   $json["brcode"]=$pix;
   $json["imagem"]="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" .urlencode($pix) . "&choe=UTF-8";
   // disable cors
   echo json_encode($json);
}
else {
   $json["erro"]="Parâmetros inválidos";
   echo json_encode($json);
}
?>