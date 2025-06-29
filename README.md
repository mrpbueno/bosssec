# Módulo Chefe-Secretária para FreePBX
Um módulo para FreePBX 14+ que implementa um fluxo de chamadas inteligente do tipo "Chefe-Secretária". As chamadas destinadas a um "Chefe" são primeiro interceptadas e direcionadas a uma "Secretária", que atua como um portão de entrada, com a flexibilidade de uma lista de permissões (whitelist) para acesso direto.

Este projeto foi desenvolvido seguindo as melhores práticas para a criação de módulos no FreePBX, utilizando a arquitetura BMO (Big Module Object)  e sendo o menos invasivo possível.

## Funcionalidades Principais
Roteamento Inteligente: Desvia chamadas destinadas a um ramal "Chefe" para um ramal "Secretária" designado.

Whitelist de Acesso Direto: Configure uma lista de números (internos ou externos) que podem contornar a secretária e ligar diretamente para o chefe.

Gerenciamento de Múltiplos Pares: A interface permite configurar múltiplas regras de Chefe-Secretária, cada uma com sua própria whitelist.

Interface Gráfica Integrada: Todas as configurações são gerenciadas através de uma nova página no menu "Applications" do FreePBX, sem a necessidade de editar arquivos de configuração manualmente.

Seleção de Ramais Amigável: Utiliza a biblioteca Select2 para facilitar a busca e seleção dos ramais do chefe e da secretária, minimizando erros.

Notificações de Feedback: Exibe mensagens de sucesso ou erro ("toast notifications") após cada ação, melhorando a experiência do usuário.

Integração Segura com o Dialplan: O módulo injeta sua lógica de forma segura no Dialplan do Asterisk, sem sobrescrever contextos existentes, garantindo compatibilidade e estabilidade.

## Como Funciona
O módulo utiliza um Dialplan Hook para se integrar ao FreePBX. Em vez de criar contextos que possam entrar em conflito com a lógica padrão, ele usa a função $ext->splice()  para injetar um comando Goto no início do contexto ext-local do ramal do chefe.

Isso desvia a chamada para uma sub-rotina personalizada que:

Verifica o CallerID do autor da chamada contra a whitelist.

Se o número for permitido, a chamada é devolvida ao fluxo padrão do FreePBX (ext-local), em uma prioridade que evita loops, para ser completada normalmente.

Se o número não for permitido, a chamada é direcionada para o fluxo padrão do ramal da secretária.

Essa abordagem garante que o módulo apenas gerencie o desvio inicial, deixando todo o resto do processamento da chamada (incluindo Siga-me, correio de voz, etc.) para o próprio FreePBX.

## Pré-requisitos

FreePBX 14 ou superior.

PHP 5.6 ou superior.

## Instalação
Baixe a última versão do módulo em https://github.com/mrpbueno/bosssec/releases

Navegue até Administrador > Admin Módulos

Clique em Carregar Módulos e faça o upload do arquivo zip do módulo

Volte a lista de módulos e faça a instalação do módulo Chefe Secretária

## Uso e Configuração
Após a instalação, o módulo estará disponível no menu do FreePBX.

Navegue até Applications > Chefe Secretaria.

Você verá uma lista de todas as regras existentes. Para adicionar uma nova, clique em "Adicionar".

Preencha o formulário:

Nome do Chefe: Um nome descritivo para a regra (ex: "Diretor Financeiro").

Ramal do Chefe: Selecione o ramal do chefe na lista.

Ramal da Secretária: Selecione o ramal da secretária para onde as chamadas serão desviadas.

Whitelist de Números: Adicione os números que terão acesso direto. Você pode separar por espaço, vírgula ou quebra de linha.

Regra Ativada: Defina se a regra está ativa ou não.

Clique em "Enviar" para salvar.

Importante: Após salvar qualquer alteração, o botão vermelho "Apply Config" aparecerá no topo da página. Clique nele para que as suas novas regras de discagem sejam aplicadas no Asterisk.

## Licença
Este projeto é licenciado sob a licença GPLv3.
