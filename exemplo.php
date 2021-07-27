Linha do Pix (copia e cola):
<PRE>
<?php
/*
# Exemplo de uso do php_qrcode_pix com descrição dos campos
#
# Desenvolvido em 2020-2021 por Renato Monteiro Batista - http://renato.ovh
#
# Este código pode ser copiado, modificado, redistribuído
# inclusive comercialmente desde que mantidos a refereência ao autor.
*/

include "phpqrcode/qrlib.php"; 
include "funcoes_pix.php";


$px[00]="01"; //Payload Format Indicator, Obrigatório, valor fixo: 01
// Se o QR Code for para pagamento único (só puder ser utilizado uma vez), descomente a linha a seguir.
//$px[01]="12"; //Se o valor 12 estiver presente, significa que o BR Code só pode ser utilizado uma vez. 
$px[26][00]="BR.GOV.BCB.PIX"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
$px[26][01]="42a57095-84f3-4a42-b9fb-d08935c86f47"; //Chave do destinatário do pix, pode ser EVP, e-mail, CPF ou CNPJ.
//$px[26][02]="Descricao"; // Descrição da transação, opcional.
/*
Outros exemplos de chaves:
CPF:
$px[26][01]="12345678901"; // CPF somente numeros.

CNPJ:
$px[26][01]="05930393000156"; // CNPJ somente numeros.

E-mail
$px[26][01]="doe@r3n4t0.cyou"; // Chave de e-mail, tamanho maximo 77. Chave, descricao e os IDs devem totalizar no máximo 99 caracteres no campo 26.

Telefone:
$px[26][01]="+5599888887777"; //Codificar o telefone no formato internacional, no exemplo: +55 Pais, 99 DDD, 888887777 telefone.
*/

$px[52]="0000"; //Merchant Category Code “0000” ou MCC ISO18245
$px[53]="986"; //Moeda, “986” = BRL: real brasileiro - ISO4217
$px[54]="10.00"; //Valor da transação, se comentado o cliente especifica o valor da transação no próprio app. Utilizar o . como separador decimal. Máximo: 13 caracteres.
$px[58]="BR"; //“BR” – Código de país ISO3166-1 alpha 2
$px[59]="RENATO MONTEIRO BATISTA"; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
$px[60]="NATAL"; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
$px[62][05]="***"; //Identificador de transação, quando gerado automaticamente usar ***. Limite 25 caracteres. Vide nota abaixo.

/*
NOTA Sobre o 62/05, identificador de transação:
Notei que logo do lançamento do pix os aplicativos não estavam implicando com esse campo e até mesmo 
os BRCodes gerados em aplicativos de alguns bancos não apresentavam esse campo.
Porém recentemente identifiquei que algumas instituições já não estão processando os pix na ausência
desse campo. Trtaado na issue https://github.com/renatomb/php_qrcode_pix/issues/2

Conforme o Manual de Padrões para Iniciação do Pix ver 2.2 na página 5, nota de rodapé, temos: "Conclui-se 
que, se o gerador do QR optar por não utilizar um  transactionID, o valor ‘***’ deverá ser usado para indicar
essa escolha." Diante disso estou atribuindo como sugestão neste exemplo, linha 38, o uso de *** no 6205.

O conteúdo desse campo é gerado pelo recebedor do pix. Devendo ser um valor único para cada transação, ou ***
quando não for usado pois esse passa a ser gerado automaticamente. Dada a necessidade de identificador único,
caso haja a opção pelo uso do mesmo recomendo a utilização de um UUID vinculado ao sistema do recebedor, o que
permitirá a conciliação dos pagamentos que foram recebidos.

Entretanto, conforme discutido na issue https://github.com/bacen/pix-api/issues/214 o Banco Itaú bloqueia
qualquer código de transação que não tenha sido gerado previamente no aplicativo da instituição. Necessário
solicitar ao gerente da conta a liberação para que a conta do recebedor possa gerar qrcode do pix fora do
aplicativo do banco. É possível que outras instituições passem a adotar esse posicionamento no futuro e até
mesmo venham a cobrar por isso.

Com o uso de QR Code dinâmicos é possível inclusive definir um webHook onde o cliente final seja notificado
automaticamente quando determinada transação for recebida. Mas para isso consulte os detalhes da API da sua
instituição.

*/

/*
O campo 62/50 é um campo facultativo, que indica a versão do arranjo de pagamentos que está sendo usada.
$px[62][50][00]="BR.GOV.BCB.BRCODE"; //Payment system specific template - GUI
$px[62][50][01]="1.0.0"; //Payment system specific template - versão
*/

//Caso queira visualizar a matriz dos dados que serão montados no pix descomente a linha a seguir.
//print_r($px);
$pix=montaPix($px);

/*
# A função montaPix prepara todos os campos existentes antes do CRC (campo 63).
# O CRC deve ser calculado em cima de todo o conteúdo, inclusive do próprio 63.
# O CRC tem 4 dígitos, então o campo será um 6304.
*/
$pix.="6304"; //Adiciona o campo do CRC no fim da linha do pix.
$pix.=crcChecksum($pix); //Calcula o checksum CRC16 e acrescenta ao final.

echo $pix;
?>
</PRE><br>
<center><h1>Imagem de QRCode do Pix</h1></center>
<?

/*
# Para montar a imagem do QRCode utilizo neste exemplo a biblioteca phpqrcode.
# Esta biblioteca permite gerar imagens QRCode diretamente em arquivos.
# No exemplo abaixo em vez de salvar a imagem em um arquivo, eu estou pegando o
# conteúdo da imagem em base64 e transmitindo diretamente ao navegador.
# Optei por fazer dessa forma a fim de não ficar gerando arquivos temporários.
*/
ob_start();
QRCode::png($pix, null,'M',5);
$imageString = base64_encode( ob_get_contents() );
ob_end_clean();
// Exibe a imagem diretamente no navegador codificada em base64.
echo '<img src="data:image/png;base64,' . $imageString . '"></center>';
?>