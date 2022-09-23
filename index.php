<?php
if (!isset($_GET["doacao"])) {
   if (isset($_POST["chave"]) && isset($_POST["beneficiario"]) && isset($_POST["cidade"])) {
      $chave_pix=strtolower($_POST["chave"]);
      $beneficiario_pix=$_POST["beneficiario"];
      $cidade_pix=$_POST["cidade"];
      if (isset($_POST["descricao"])){
         $descricao=$_POST["descricao"];
      }
      else { $descricao=''; }
      if ((!isset($_POST["identificador"])) || (empty($_POST["identificador"]))) {
         $identificador="***";
      }
      else {
         /*
         Atenção: Quando informado pelo recebedor, cada identificador deve ser único (ex.: UUID).
         Os identificadores são usados para a facilitar a conciliação da transação. Na auséncia do
         identificador recomendável o uso de três astericos: ***
         O identificador é limitado a 25 caracteres.
         */
         $identificador=$_POST["identificador"];
         if (strlen($identificador) > 25) {
            $identificador=substr($identificador,0,25);
         }
      }
      $gerar_qrcode=true;
   }
   else {
      $cidade_pix="SAO PAULO";
      $gerar_qrcode=false;
   }
}
else {
   $chave_pix="42a57095-84f3-4a42-b9fb-d08935c86f47";
   $beneficiario_pix="RENATO MONTEIRO BATISTA";
   $cidade_pix="NATAL";
   $identificador="***";
   $descricao="Demo phpQRCodePix";
   $gerar_qrcode=true;
}
if (is_numeric($_POST["valor"])){
   $valor_pix=preg_replace("/[^0-9.]/","",$_POST["valor"]);
}
else {
   $valor_pix="0.00";
}
?>

<!doctype html>
<html lang="pt-br">
<head>
<title>Gerador de QR Code do PIX</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Gerador gratuito de QR Code e BR Code do Pix. Gere o seu QR Code ou a linha digitável do Pix Copia e Cola.">
<meta name="keywords" content="pix, qrcode pix, qr code, br code, brcode pix, pix copia e cola" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
<script src="https://kit.fontawesome.com/0f8eed42e7.js" crossorigin="anonymous"></script>
<script>
function copiar() {
  var copyText = document.getElementById("brcodepix");
  copyText.select();
  copyText.setSelectionRange(0, 99999); /* For mobile devices */
  document.execCommand("copy");
  document.getElementById("clip_btn").innerHTML='<i class="fas fa-clipboard-check"></i>';
}
function reais(v){
    v=v.replace(/\D/g,"");
    v=v/100;
    v=v.toFixed(2);
    return v;
}
function mascara(o,f){
    v_obj=o;
    v_fun=f;
    setTimeout("execmascara()",1);
}
function execmascara(){
    v_obj.value=v_fun(v_obj.value);
}
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-E6M96X7Y2Y"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-E6M96X7Y2Y');
</script>
<style>
a {text-decoration: none;} 
p {text-align: center;}
</style>
</head>
<body>
<?php
/*
# Exemplo de uso do php_qrcode_pix com descrição dos campos
#
# Desenvolvido em 2020 por Renato Monteiro Batista - http://renato.ovh
#
# Este código pode ser copiado, modificado, redistribuído
# inclusive comercialmente desde que mantidos a refereência ao autor.
*/
if ($gerar_qrcode){
   include "phpqrcode/qrlib.php"; 
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
   $linhas=round(strlen($pix)/120)+1;
   ?>
   <div class="card">
   <h3>Linha do Pix (copia e cola):</h3>
   <div class="row">
      <div class="col">
      <textarea class="text-monospace" id="brcodepix" rows="<?= $linhas; ?>" cols="130" onclick="copiar()"><?= $pix;?></textarea>
      </div>
      <div class="col md-1">
      <p><button type="button" id="clip_btn" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Copiar código pix" onclick="copiar()"><i class="fas fa-clipboard"></i></button></p>
      </div>
   </div>
   </div>
   <h3>Imagem de QRCode do Pix:</h3>
   <p>
   <img src="logo_pix.png"><br>
   <?php
   ob_start();
   QRCode::png($pix, null,'M',5);
   $imageString = base64_encode( ob_get_contents() );
   ob_end_clean();
   // Exibe a imagem diretamente no navegador codificada em base64.
   echo '<img src="data:image/png;base64,' . $imageString . '"></p>';
}
?>
<h3>Gerador de QR Code do PIX</h3>
<div class="card">
<div class="card-body">
<form method="post" action="index.php">
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="chave" class="form-label">Chave Pix:</label>
      <input type="text" id="chave" name="chave" placeholder="Informe a chave pix" value="<?= $chave_pix;?>" size="50" maxlength="100" onclick="this.select();" data-toggle="tooltip" data-placement="right" title="Informe a chave pix de destino" required>
      <div id="chaveHelp" class="form-text">A chave pode ser: Aleatória (EVP), E-mail, Telefone, CPF ou CNPJ.</div>
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="valor" class="form-label">Valor a pagar:</label>
      <input type="text" id="valor" name="valor" placeholder="Informe o valor a cobrar" size="15" maxlength="13" value="<?= $valor_pix; ?>" onclick="this.select();" onkeypress="mascara(this,reais)">
      <div id="valorHelp" class="form-text">Utilize o ponto "." como separador de decimais. Prencher 0 caso não deseje especificar um valor.</div>
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="beneficiario" class="form-label">Nome do beneficiário:</label>
      <input type="text" id="beneficiario" name="beneficiario" placeholder="Informe o nome do beneficiario" size="30"  onclick="this.select();" maxlength="25" value="<?= $beneficiario_pix; ?>" required >
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="beneficiario" class="form-label">Cidade do beneficiário:</label>
      <input type="text" name="cidade" placeholder="Informe a cidade" onclick="this.select();" maxlength="15" value="<?= $cidade_pix;?>" required>
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="descricao" class="form-label">Descrição da cobrança (opcional):</label>
      <input type="text" id="descricao" name="descricao" placeholder="Descricao do pagamento" size="60" maxlength="70" value="<?= $_POST["descricao"];?>" value="<?= $_POST["descricao"];?>" onclick="this.select();">
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="identificador" class="form-label">Identificador do pagamento:</label>
      <input type="text" id="identificador" name="identificador" placeholder="Identificador do pagamento" value="***" size="25" onclick="this.select();" value="<?= $_POST["identificador"];?>" >
      <div id="identificadorHelp" class="form-text">Utilizar <b>***</b> para identificador gerado automaticamente.O Banco Itaú exige a autorização para uso de identificador que não tenha sido criado pelo aplicativo do próprio banco, <a href="https://github.com/bacen/pix-api/issues/214">saiba mais</a>.</div>
   </div>
   <p><button type="submit" class="btn btn-primary">Gerar QR Code <i class="fas fa-qrcode"></i></button>&nbsp;<a href="?doacao" class="btn btn-info">Ajude a manter este projeto <i class="fas fa-hand-holding-usd"></i></a>&nbsp;<a href="https://decoderpix.dinheiro.tech/" class="btn btn-info">Decodificador BR Code Pix <i class="fas fa-hammer"></i></a></p>
</form>
</div></div>
<div class="card">
<p>Este é um projeto opensource criado em 2020 por <i class="fas fa-user-secret"></i> <a href="http://renato.ovh" target="_blank">Renato Monteiro Batista</a> executado nos servidores <i class="fas fa-server"></i> da <a href="http://rmbinformatica.com" target="_blank">RMB Informática</a>.</p>
<p>O código fonte <i class="fas fa-code"></i> está disponível no <a href="https://github.com/renatomb/php_qrcode_pix" target="_blank">Repositório <i class="fab fa-git-square"></i> php_qrcode_pix <i class="fab fa-github"></i></a>. Versão Demo 1.0.2.</p>
</div>
</body>
</html>