# php qrcode pix

Este repositório contém o conjunto de código necessário à implementação do QRCode de recebimento de pagamentos do [PIX do Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix) em PHP.

## Dependências

Para a geração do QRCode foi usada a biblioteca [PHP QRCode](http://phpqrcode.sourceforge.net/). O conteúdo da biblioteca está no diretório `phpqrcode`.

## Introdução ao código do PIX

Conforme o [manual de implementação do BR Code](https://www.bcb.gov.br/content/estabilidadefinanceira/SiteAssets/Manual%20do%20BR%20Code.pdf) o Pix adota a representação de dados estruturados de pagamento proposta no padrão
EMV®1.

Recomendo a leitura do manual em questão para obter informações adicionais da implementação.

O pagamento através do pix pode ser feito de forma manual com a digitação dos dados do recebedor ou de maneira automatizada onde o recebedor disponibiliza uma requisição de pagamento que será lida pela instituição do pagador. Essa requisição de pagamentyo pode ser em formato texto, que foi denominado Pix Copia e Cola, ou através de um QRCode contendo o mesmo texto do Pix Copia e Cola.

### Formação do código de pagamento

O código de pagamento é um campo de texto alfanumérico (A-Z,0-9) permitindo os caracteres especiais `$ % * + - . / :`.

Na estrutura EMV®1 os dois primeiros dígitos representam o código ID do emv e os dois dígitos seguintes contendo o tamanho do campo. O conteúdo do campo são os caracteres seguintes até a quantidade de caracteres estabelecida.

#### Exemplos de código EMV

No código `000200` temos:

* `00` Código EMV 00 que representa o Payload Format Indicator;
* `02` Indica que o conteúdo deste campo possui dois caracteres;
* `00` O conteúdo deste campo é 00.

No código `5303986` temos:

* `53` Código EMV 53 que indica a Transaction Currency, ou seja: a moeda da transação.
* `03` Indica que o tamanho do campo possui três caracteres;
* `986` Conteúdo do campo é 986, que é o código para  BRL: real brasileiro na ISO4217.

No código `5802BR` temos:

* `58` Código EMV 58 que indica o Country Code.
* `02` Indica que o tamanho do campo possui dois caracteres;
* `BR` Conteúdo do campo é BR, que é o código do país Brasil conforme  ISO3166-1 alpha 2.

Um pix copia e cola contendo os somente os campos acima ficaria `00020053039865802BR`, não há qualquer espaço ou outro caractere separando os campos pois o tamanho de cada campo já está especificado logo após o ID, sendo possível fazer o processamento.

### Especificades do BR Code

O Pix utiliza o padrão BR Code do banco central, em especial os campos de ID 26 a 51. Esses campos possuem *filhos* que seguem o mesmo padrão do EMV explicado acima.

#### Exemplos BR Code

Observe o código: `26580014br.gov.bcb.pix013642a57095-84f3-4a42-b9fb-d08935c86f47`, nele há:

* `26` Código EMV 26 que representa o Merchant Account Information.
* `58` Indica que o tamanho do campo possui 58 caracteres.
* Demais caracteres representam o conteúdo do campo `0014br.gov.bcb.pix013642a57095-84f3-4a42-b9fb-d08935c86f47`.

Nele, temos dois *filhos*:

O primeiro é `0014br.gov.bcb.pix`:

* `00` ID 00 representa o campo GUI do BRCode (obrigatório).
* `14` Indica que o tamanho do campo possui 14 caracteres.
* `br.gov.bcb.pix` é conteúdo do campo.

O segundo é `013642a57095-84f3-4a42-b9fb-d08935c86f47`:

* `01` O ID 01 representa a chave PIX, que pode ser uma chave aleatória (EVP), e-mail, telefone, CPF ou CNPJ.
* `36` Indica que o tamanho do campo possui 36 caracteres.
* `42a57095-84f3-4a42-b9fb-d08935c86f47` indica a chave pix do destinatário, no caso a chave em questão está no formato UUID que é uma chave aleatória (EVP).

Se você está apreciando o conteúdo deste trabalho, sinta-se livre para fazer qualquer doação para a chave `42a57095-84f3-4a42-b9fb-d08935c86f47` :)

## Implementação

Esta implementação foi feita através do uso de pequenas funções simples, de maneira estruturada sem classes e objetos buscando atingir o maior número de pessoas.

Para gerar a linha do pix copia e cola, coloque os valores desejados em cada campo em um vetor qualquer onde o índice é o código ID do EVP. Caso o campo tenha filhos basta incluir mais uma dimensão no vetor. Passe o vetor como parâmetro para a função `montaPix`.

A função irá retornar a linha digitável do pix semi-completa, faltando apenas o campo do CRC que deve ser adicionado no campo 63 possuindo o tamanho de 4 bytes. Dessa forma o código 6304 deve estar inserido na função para cálculo do CRC `crcChecksum`.

Exemplo:

```php
<?php
$pix=montaPix($px);
$pix.="6304";
$pix.=crcChecksum($pix);
?>
```

Após gerada a linha do pix copia e cola ela pode ser encaminhada para o pagador (whatsapp, e-mail) ou enviada para o gerador de qrcode.

No arquivo `exemplo.php` há um exemplo mais completo e com comentários a cerca de alguns dos campos possíveis. Para informações mais completas dos campos consulte a documentação oficial do [Bacen](https://bcb.gov.br).

## Nota sobre o uso de chaves EVP

As chaves aleatórias (Endereço Virtual de Pagamento - EVP) diferenciam letras maiúsculas de minúsculas.

## Nota sobre o uso do identificador

### Itaú

Nos testes foi observado que o itaú só aceita receber o pix se o identificador, valor e descrição existentes no qrcode forem os mesmos previamente cadastrados no sistema deles. Não foi encontrada nenhuma documentação pública a cerca de alguma API para efetuar esse cadastro.

## Testes realizados

Esta implementação foi testada, realizando a leitura do QRCode gerado, nos aplicativos dos seguintes bancos:

* Banco Inter;
* Bradesco;
* Sofisa direto;
* NuBank;
* BS2;
* C6;
* Banco do Brasil;
* Santander;
* Safra Wallet (AgZero);
* BMG;
* Sicredi;
* Itau;
* PagBank;
* AgiBank;
* Digio.

## Autor

Desenvolvido em 2020 por [Renato Monteiro Batista](https://renato.ovh).