<?php
if (!isset($_POST["doacao"])) {
   if (isset($_POST["chave"]) && isset($_POST["beneficiario"]) && isset($_POST["cidade"])) {
      $chave_pix=$_POST["chave"];
      $beneficiario_pix=$_POST["beneficiario"];
      $cidade_pix=$_POST["cidade"];
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
   $gerar_qrcode=true;
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
<script>
function copiar() {
  var copyText = document.getElementById("brcodepix");
  copyText.select();
  copyText.setSelectionRange(0, 99999); /* For mobile devices */
  document.execCommand("copy");
}
</script>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-E6M96X7Y2Y"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-E6M96X7Y2Y');
</script>
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
   $px[26][00]="BR.GOV.BCB.PIX"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
   $px[26][01]=$chave_pix;
   $px[52]="0000"; //Merchant Category Code “0000” ou MCC ISO18245
   $px[53]="986"; //Moeda, “986” = BRL: real brasileiro - ISO4217
   if (is_numeric($_POST["valor"])) {
      $px[54]=$_POST["valor"];
   }
   else {
      $px[54]="0.00";
   }
   $px[58]="BR"; //“BR” – Código de país ISO3166-1 alpha 2
   $px[59]=$beneficiario_pix; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
   $px[60]=$cidade_pix; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
   if (isset($_POST["identificador"]) && !empty($_POST["identificador"])){
      $px[62][05]=$_POST["identificador"];
      $px[62][50][00]="BR.GOV.BCB.BRCODE"; //Payment system specific template - GUI
      $px[62][50][01]="1.0.0"; //Payment system specific template - versão
   }
   $pix=montaPix($px);
   $pix.="6304"; //Adiciona o campo do CRC no fim da linha do pix.
   $pix.=crcChecksum($pix); //Calcula o checksum CRC16 e acrescenta ao final.
   ?>
   <div class="card">
   <h3>Linha do Pix (copia e cola):</h3>
   <div class="row">
      <div class="col">
      <textarea class="text-monospace" id="brcodepix" rows="4" cols="130"><?= $pix;?></textarea>
      </div>
      <div class="col md-1">
      <center>
      <button type="button" class="btn-clipboard" title="" data-bs-original-title="Copiar código pix" onclick='copiar()'>Copiar</button>
      </center>
      </div>
   </div>
   </div>
   <h3>Imagem de QRCode do Pix:</h3>
   <center>
   <?php
   ob_start();
   QRCode::png($pix, null,'M',5);
   $imageString = base64_encode( ob_get_contents() );
   ob_end_clean();
   // Exibe a imagem diretamente no navegador codificada em base64.
   echo '<img src="data:image/png;base64,' . $imageString . '"></center>';
}
?>
<h3>Gerador de QR Code do PIX</h3>
<div class="card">
<div class="card-body">
<form method="post">
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="chave" class="form-label">Chave Pix:</label>
      <input type="text" id="chave" name="chave" placeholder="Informe a chave pix" value="<?= $chave_pix;?>" size="50" maxlength="100" onclick="this.select();" required>
      <div id="chaveHelp" class="form-text">A chave pode ser: Aleatória (EVP), E-mail, Telefone, CPF ou CNPJ.</div>
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="valor" class="form-label">Valor a pagar:</label>
      <input type="number" id="valor" min="0.00" step="0.01" name="valor" placeholder="Informe o valor a cobrar" size="15" maxlength="13" value="<?= (is_numeric($_POST["valor"])?$_POST["valor"]:"0.00"); ?>" onclick="this.select();">
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
      <input type="text" id="descricao" name="descricao" placeholder="Descricao do pagamento" size="60" maxlength="100" value="<?= $_POST["descricao"];?>" value="<?= $_POST["descricao"];?>" onclick="this.select();">
   </div>
   <div class="row row-cols-lg-auto g-3 align-items-center">
      <label for="identificador" class="form-label">Identificador do pagamento (opcional):</label>
      <input type="text" id="identificador" name="identificador" placeholder="Identificador do pagamento" size="25" onclick="this.select();" value="<?= $_POST["identificador"];?>" >
      <div id="identificadorHelp" class="form-text">Se a conta destino for do Banco Itaú não utilize o identificador ou o pix poderá ser recusado.</div>
   </div>
   <p align="center"><input type="submit" value="Gerar QR Code" class="btn btn-primary"></p>
</form>
<form method="POST">
<p align="center"><input type="submit" value="Faça uma doação" name="doacao" value="y" class="btn btn-info"></p>
</form>
</div></div>
<div class="card">
<p align="center">Este é um projeto opensource criado em 2020 por <a href="http://renato.ovh">Renato Monteiro Batista</a> executado nos servidores da <a href="http://rmbinformatica.com">RMB Informática</a>, o código fonte está disponível no <a href="https://github.com/renatomb/php_qrcode_pix">Repositório php_qrcode_pix do Github</a>.</p>
</div>
</body>
</html>