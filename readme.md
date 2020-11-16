# php qrcode pix

Este repositório contém o conjunto de código necessário à implementação do QRCode de recebimento de pagamentos do [PIX do Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix) em PHP.

## Dependências

Para a geração do QRCode foi usada a biblioteca [PHP QRCode](http://phpqrcode.sourceforge.net/). O conteúdo da biblioteca está no diretório `phpqrcode`.

## Introdução
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

## Testes

Esta implementação foi testada, realizando a leitura do QRCode gerado nos aplicativos dos seguintes bancos:

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
