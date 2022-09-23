# php qrcode pix

Este repositório contém o conjunto de código necessário à implementação do QRCode de recebimento de pagamentos do [PIX do Banco Central](https://www.bcb.gov.br/estabilidadefinanceira/pix) em PHP.

## Versão live demo

Estou disponibilizando a versão live deste repositório no site [Gerador de QR Code do pix](http://qrcodepix.dinheiro.tech), bem como do [Decodificador de BR Code do Pix](https://decoderpix.dinheiro.tech/) ambos para uso gratuito para fins de testes.

Não utilizem a versão demo em ambiente de produção pois esse não é o propósito dela. Algumas modificações recentes na formação do código pix já causaram mal funcionamento na demo até o momento que fui capaz de identificar o problema.

## Dependências

Para a geração do QRCode foi usada a biblioteca [PHP QRCode](http://phpqrcode.sourceforge.net/). O conteúdo da biblioteca está no diretório `phpqrcode`.

## Introdução ao código do PIX

Conforme o [manual de implementação do BR Code](doc/ManualDoBRCode.pdf) o Pix adota a representação de dados estruturados de pagamento proposta no padrão EMV®1.

Recomendo a leitura do manual em questão para obter informações iniciais sobre a implementação.

Para se aprofundar nos detalhes técnicos ou se quiser informações sobre os QR Codes dinâmicos também
recomendo a leitura do [Manual de Padrões para Iniciação do Pix](doc/ManualDePadroesParaIniciacaoDoPix.pdf).

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

Para facilitar a visualização de um código EMV a partir de qualquer Pix Copia-e-Cola, estou disponibilizando
também o [Decodificador do Pix Copia-e-Cola](https://decoderpix.dinheiro.tech/) cujo código fonte está
no repositório [decoder_brcode_pix](https://github.com/renatomb/decoder_brcode_pix).

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

Também estou disponibilizando uma versão live/demo no site [Gerador de QR Code do PIX](http://qrcodepix.dinheiro.tech/).

## Nota sobre o uso de chaves EVP

As chaves aleatórias (Endereço Virtual de Pagamento - EVP) devem ser informadas em letras minúsculas.

## Nota sobre o uso da descrição do pagamento (campo 26 02)

A descrição do pagamento é exibida para o pagador no ato da confirmação do pix no aplicativo do cliente, nos bancos abaixo-relacionados essa informação consta no extrato da conta de quem recebeu o pix:

* Nubank;

## Nota sobre o uso do identificador de transação

Conforme o manual [manual de implementação do BR Code](doc/ManualDoBRCode.pdf), pg 5, nota de rodapé, temos: "Conclui-se que, se o gerador do QR optar por não utilizar um  transactionID, o valor `***` deverá ser usado para indicar essa escolha.

### Nubank

O identificador usado não é exibido no extrato da NuConta. A descrição da transação (campo 26 02) é facilmente
identificável no aplicativo.

### Itaú

Itaú recusa o pix de qualquer identificador de transação que não tenha sido gerado previamente no aplicativo deles. Conforme [informações que obtive](https://github.com/bacen/pix-api/issues/214) para utilizar qr code gerado fora do aplicativo do itaú, é necessário entrar em contato com o gerente para que o mesmo realize a liberação da conta para uso de qrcoe de terceiros. Se não houver essa liberação o Itaú está recusando o recebimento do pix com base no identificador utilizado.

## Testes realizados

Esta implementação foi testada, realizando a leitura do QRCode, Pix Copia-e-Cola, envio de Pix para outra instituição e Recebimento de pix de outra instituição, nos aplicativos dos seguintes bancos:

* [Banco Inter](https://www.bancointer.com.br/convite-abrir-conta/?c=cElVNw2WQb);
* [Sofisa direto](https://sd.sofisadireto.com.br/MGM/IndiqueEGanhe/?codigo=RMB0283599);
* [NuBank](https://nubank.com.br/indicacao/nu/?id=_WBz8C2qwOcAAAF3o3bmBg&msg=cb40c&utm_channel=social&utm_medium=referral&utm_source=mgm);
* [C6 Bank](https://c6bank.onelink.me/fSbV/c6indica);
* [AgZero / Safra Wallet](https://banco.dinheiro.tech/bancos-nacionais:agzero);
* [BMG](https://banco.dinheiro.tech/bancos-nacionais:bmg);
* [PagBank](https://indicapagbank.page.link/EfW5F);
* [Digio](https://digio.com.br/convite/?id=3c387eb1&utm_source=mgm&utm_medium=convite&utm_campaign=cartao-credito-indica);
* [MercadoPago](http://mpago.li/1JxaWKH);
* Itau;
* Bradesco;
* BS2;
* Banco do Brasil;
* Santander;
* Sicredi;
* AgiBank;
* GerenciaNet;

## Autor

Desenvolvido em 2020/2021 por [Renato Monteiro Batista](https://renato.ovh).

## Agradecimentos

Agradeço inicialmente a todos aqueles que puderem contribuir com o projeto mandando um pix. :)

* A todos que doaram um pix para incentivar na continuidade deste projeto.
* Ao Banco Santander por ter a melhor implementação do pix na minha opinião.
* Agradecimento ao [Rodrigo Fleury](https://github.com/rfbastos) por identificar a questão do identificador de transação com o Banco Itaú.
* A Micro Reis Informática pelos testes do GerenciaNet.
* Ao Banco itaú por ser a implementação mais implicante do pix.
* A todos que de alguma forma colaboraram para esse projeto.

## Disclaimer

Este é um projeto hobby, feito em caráter voluntário, a fim de oferecer um norte inicial a quem quiser implementar recebimentos por pix. Não tenho vínculo com o BACEN nem com nenhuma instituição financeira.

Procurei explicar todo meu entendimento, até o momento, na forma de comentários no código mas sem a pretensão de esgotar o assunto. Caso você identifique que algo mudou o que eu apresentei algum conceito de forma erronea me coloco a disposição para fazer os ajustes necessários, basta me contactar via [telegram](http://t.me/r3n4t0).

## Documentação oficial

Em virtude do fato do BACEN ter optado por retirar do ar / mudar a url das documentações que eu havia referenciado anteriormente. Estou optando por anexar tais arquivos a esse projeto na pastas `doc` a documentação oficial sobre o pix que tive acesso. Caso vocë tenha acesso a alguma outra documentação relevante que possa ser anexada a este projeto favor fazer um pull request.

Recomendo a todos a leitura de toda a documentação anexada a fim de esclarecer algumas dúvidas iniciais. Esse projeto se propõe a oferecer uma camada entre os dados do recebedor criando o código bruto de um Pix-Copia-e-Cola (BRCode) e a geração da imagem do QR Code, mas se determinados requisitos mínimos não forem seguidos é possível que o código gerado não seja aceito nos aplicativos das instituições financeiras.

* [manual de implementação do BR Code ver 1.0.0](doc/ManualDoBRCode.pdf)
* [Manual de Padrões para Iniciação do Pix ver 2.2](doc/ManualDePadroesParaIniciacaoDoPix.pdf)
* [Especificações técnicas e de negócio do ecossistema de pagamentos instantâneos brasileiro, Anexo I – Padrões para Iniciação do PIX ver 1.2.006](doc/PadroesParaIniciacaoDoPIX.pdf)

## Onde obter ajuda

Para ajuda sobre alguma implementação específica, recomendo iniciar o contato inicialmente com a sua instituição financeira pois a maioria delas já possuem soluções prontas para a maioria das implementações.

Dúvidas sobre a api do pix consulte os [Repositórios do BACEN no github](https://github.com/bacen) e verifique os canais oficiais de ajuda.

Outros questinamentos a cerca do meu código ou sugestões de melhoria pode entrar em contato diretamente comigo através de uma [issue](https://github.com/renatomb/php_qrcode_pix/issues) ou do [telegram](http://t.me/r3n4t0).