# Contribuindo para o CodeFlow Logger

Obrigado por considerar contribuir para o CodeFlow Logger! Este documento fornece diretrizes e informações sobre como contribuir para o projeto.

## 📋 Índice

- [Código de Conduta](#código-de-conduta)
- [Como Começar](#como-começar)
- [Processo de Contribuição](#processo-de-contribuição)
- [Diretrizes de Código](#diretrizes-de-código)
- [Testes](#testes)
- [Pull Requests](#pull-requests)
- [Reportar Bugs](#reportar-bugs)
- [Solicitar Features](#solicitar-features)

## 📜 Código de Conduta

Este projeto segue um código de conduta que esperamos que todos os contribuidores sigam. Seja respeitoso, profissional e construtivo em todas as interações.

## 🚀 Como Começar

### Pré-requisitos

- **PHP** >= 7.4
- **Composer** >= 2.0
- **Git**
- **Docker** (opcional, mas recomendado para desenvolvimento)

### Configuração Inicial

1. **Fork o repositório** no GitHub

2. **Clone seu fork:**
```bash
git clone https://github.com/seu-usuario/logger.git
cd logger
```

3. **Adicione o repositório upstream:**
```bash
git remote add upstream https://github.com/codeflow-hub/logger.git
```

4. **Instale as dependências:**
```bash
composer install
```

5. **Verifique se tudo está funcionando:**
```bash
composer test
composer phpstan
composer cs-check
```

## 🔄 Processo de Contribuição

### 1. Criar uma Branch

Sempre crie uma nova branch a partir da `main`:

```bash
# Atualizar main
git checkout main
git pull upstream main

# Criar nova branch
git checkout -b feature/sua-nova-feature
# ou
git checkout -b fix/correcao-do-bug
# ou
git checkout -b docs/melhorar-documentacao
```

**Convenção de nomes de branches:**
- `feature/` - Nova funcionalidade
- `fix/` - Correção de bug
- `docs/` - Documentação
- `refactor/` - Refatoração
- `test/` - Melhorias em testes
- `chore/` - Manutenção

### 2. Desenvolver

Durante o desenvolvimento:

- ✅ Siga os padrões PSR-12
- ✅ Adicione testes para novas funcionalidades
- ✅ Mantenha a documentação atualizada
- ✅ Execute os checks de qualidade regularmente
- ✅ Faça commits pequenos e frequentes

### 3. Desenvolvimento com Docker (Recomendado)

O projeto inclui configuração Docker para um ambiente isolado:

```bash
# Iniciar ambiente
docker-compose up -d

# Executar comandos dentro do container
docker-compose exec web composer test
docker-compose exec web composer phpstan
docker-compose exec web composer cs-check
docker-compose exec web composer quality
```

### 4. Verificar Qualidade

Antes de fazer commit, sempre execute os checks de qualidade:

```bash
# Executar todos os checks (recomendado)
composer quality

# Ou individualmente:
composer test              # Executa testes PHPUnit
composer test-coverage     # Gera relatório de cobertura
composer phpstan           # Análise estática (level 7)
composer cs-check          # Verifica code style (PSR-12)
composer cs-fix            # Auto-corrige problemas de estilo
```

**Importante:** Todos os checks devem passar antes de abrir um Pull Request.

### 5. Commit

Use [Conventional Commits](https://www.conventionalcommits.org/) para mensagens de commit:

```bash
git add .
git commit -m "feat: adicionar suporte a campos sensíveis customizados"
git commit -m "fix: corrigir sanitização de arrays aninhados"
git commit -m "docs: atualizar exemplos de uso"
git commit -m "test: adicionar testes para modo PARTIAL"
```

### 6. Push e Pull Request

```bash
# Push para seu fork
git push origin feature/sua-nova-feature
```

Depois, abra um Pull Request no GitHub com:
- ✅ Descrição clara das mudanças
- ✅ Referência a issues relacionadas (se houver)
- ✅ Testes que cobrem as alterações
- ✅ Atualização da documentação (se necessário)
- ✅ Screenshots/exemplos (se aplicável)

## 📋 Diretrizes de Código

### Code Style

O projeto segue estritamente o padrão **PSR-12** (Extended Coding Style Guide).

#### Regras Principais:

- **Indentação:** 3 espaços (não tabs)
- **Linha máxima:** 120 caracteres
- **Naming conventions:**
  - Classes: `PascalCase`
  - Métodos: `camelCase`
  - Constantes: `UPPER_SNAKE_CASE`
  - Variáveis: `camelCase`
- **Chaves:** Abertura na mesma linha para classes/métodos
- **Espaçamento:** 1 linha em branco entre métodos

#### Exemplo de Código:

```php
<?php

namespace CodeFlowHub\Logger;

class Example
{
   private const EXAMPLE_CONSTANT = 'value';
   
   private string $property;
   
   public function exampleMethod(array $data): void
   {
      if (empty($data))
      {
         return;
      }
      
      $this->processData($data);
   }
   
   private function processData(array $data): void
   {
      // Implementação
   }
}
```

#### Auto-correção:

Use o CodeSniffer para auto-corrigir problemas de estilo:

```bash
composer cs-fix
```

### Documentação

- **PHPDoc obrigatório** para todas as classes, métodos e propriedades públicas
- Use tags apropriadas: `@param`, `@return`, `@throws`, `@example`
- Documente a **intenção**, não apenas o que o código faz
- Mantenha exemplos atualizados

#### Exemplo de PHPDoc:

```php
/**
 * Adiciona campos sensíveis customizados que serão sanitizados automaticamente.
 *
 * Os campos adicionados são combinados com os campos padrão (password, token, secret, senha, hash).
 * Campos duplicados são automaticamente removidos. A verificação é case-insensitive.
 *
 * @param array $fields Array de strings com nomes de campos sensíveis a serem adicionados.
 * @return void
 * @example Logger::addSensitiveFields(['credit_card', 'ssn', 'api_key']);
 */
public static function addSensitiveFields(array $fields): void
{
   // Implementação
}
```

### Commits

Use [Conventional Commits](https://www.conventionalcommits.org/) para mensagens de commit:

#### Tipos de Commit:

- **`feat:`** - Nova funcionalidade
- **`fix:`** - Correção de bug
- **`docs:`** - Mudanças na documentação
- **`style:`** - Formatação, ponto e vírgula faltando, etc. (não afeta código)
- **`refactor:`** - Refatoração de código de produção
- **`test:`** - Adição ou correção de testes
- **`chore:`** - Mudanças em ferramentas de build, dependências, etc.
- **`perf:`** - Melhorias de performance
- **`ci:`** - Mudanças em CI/CD

#### Formato:

```
<tipo>(<escopo>): <descrição curta>

<descrição detalhada (opcional)>

<rodapé (opcional)>
```

#### Exemplos:

```bash
feat(logger): adicionar suporte a campos sensíveis customizados

Permite que usuários adicionem seus próprios campos que serão
automaticamente sanitizados nos logs.

Closes #123

fix(sanitization): corrigir sanitização de arrays aninhados

Corrige bug onde campos sensíveis em arrays aninhados não eram
sanitizados corretamente.

Fixes #456

docs(readme): atualizar exemplos de uso

Adiciona exemplos práticos de casos de uso comuns.
```

## 🧪 Testes

### Requisitos

- ✅ **Cobertura mínima:** 80%
- ✅ **Todos os testes devem passar**
- ✅ **Novos testes para novas funcionalidades**
- ✅ **Testes para correções de bugs**

### Estrutura de Testes

Os testes estão em `tests/LoggerTest.php` e seguem a estrutura:

```php
<?php

namespace CodeFlowHub\Logger\Tests;

use CodeFlowHub\Logger\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
   protected function setUp(): void
   {
      // Configuração antes de cada teste
   }
   
   public function testFeatureName(): void
   {
      // Arrange
      // Act
      // Assert
   }
}
```

### Executando Testes

```bash
# Executar todos os testes
composer test

# Com cobertura de código
composer test-coverage

# Ver relatório HTML de cobertura
# Abra: build/coverage/index.html
```

### Escrevendo Testes

1. **Nome descritivo:** `testAddSensitiveFieldsWithNestedArrays()`
2. **Uma responsabilidade:** Cada teste verifica uma coisa
3. **AAA Pattern:** Arrange, Act, Assert
4. **Isolamento:** Testes não devem depender uns dos outros
5. **Limpeza:** Use `setUp()` e `tearDown()` quando necessário

#### Exemplo:

```php
public function testAddSensitiveFieldsSanitizesCustomFields(): void
{
   // Arrange
   Logger::addSensitiveFields(['credit_card', 'ssn']);
   
   // Act
   $reflection = new \ReflectionClass(Logger::class);
   $method = $reflection->getMethod('sanitizeLogParams');
   $method->setAccessible(true);
   $result = $method->invoke(null, [
      'credit_card' => '1234-5678',
      'ssn' => '123-45-6789'
   ]);
   
   // Assert
   $this->assertEquals('[redacted]', $result['credit_card']);
   $this->assertEquals('[redacted]', $result['ssn']);
}
```

### Testes com Sistema de Arquivos Virtual

O projeto usa `vfsstream` para testes que envolvem arquivos:

```php
use org\bovigo\vfs\vfsStream;

protected function setUp(): void
{
   $this->fileSystem = vfsStream::setup('logs');
   // Configuração adicional
}
```

## 🔀 Pull Requests

### Checklist Antes de Abrir um PR

- [ ] Código segue PSR-12
- [ ] Todos os testes passam (`composer test`)
- [ ] Cobertura de testes >= 80%
- [ ] PHPStan passa sem erros (`composer phpstan`)
- [ ] CodeSniffer passa (`composer cs-check`)
- [ ] Documentação atualizada (README, PHPDoc)
- [ ] Commits seguem Conventional Commits
- [ ] Branch está atualizada com `main`
- [ ] Não há conflitos de merge

### Template de Pull Request

```markdown
## Descrição

Breve descrição das mudanças realizadas.

## Tipo de Mudança

- [ ] Nova funcionalidade (não quebra compatibilidade)
- [ ] Correção de bug (não quebra compatibilidade)
- [ ] Breaking change (mudança que quebra compatibilidade)
- [ ] Documentação

## Como Foi Testado?

Descreva os testes que você executou para verificar suas mudanças.

## Checklist

- [ ] Meu código segue as diretrizes do projeto
- [ ] Revisei meu próprio código
- [ ] Comentei código complexo
- [ ] Minhas mudanças não geram warnings
- [ ] Adicionei testes que provam que minha correção é efetiva ou que minha feature funciona
- [ ] Testes novos e existentes passam localmente
- [ ] Qualquer mudança dependente foi mergeada e publicada

## Screenshots (se aplicável)

Adicione screenshots para ajudar a explicar sua mudança.

## Issues Relacionadas

Closes #123
Relates to #456
```

### Processo de Code Review

1. **Aguarde feedback** - Mantenha-se disponível para responder perguntas
2. **Faça alterações** - Se solicitado, faça as mudanças e force-push
3. **Mantenha o PR atualizado** - Rebase com `main` se necessário:

```bash
git checkout main
git pull upstream main
git checkout feature/sua-nova-feature
git rebase main
git push origin feature/sua-nova-feature --force-with-lease
```

4. **Aguarde aprovação** - Pelo menos um mantenedor deve aprovar
5. **Merge** - Um mantenedor fará o merge quando tudo estiver OK

### Após o Merge

- ✅ Delete sua branch local: `git branch -d feature/sua-nova-feature`
- ✅ Delete sua branch remota: `git push origin --delete feature/sua-nova-feature`
- ✅ Atualize `main`: `git checkout main && git pull upstream main`

## 🐛 Reportar Bugs

### Antes de Reportar

1. Verifique se o bug já foi reportado nas [Issues](https://github.com/codeflow-hub/logger/issues)
2. Tente reproduzir o bug na versão mais recente
3. Verifique se não é um problema de configuração

### Template de Bug Report

Use este template ao criar uma issue:

```markdown
**Descrição do Bug**
Uma descrição clara e concisa do bug.

**Como Reproduzir**
Passos para reproduzir o comportamento:
1. Configure o logger com '...'
2. Execute '...'
3. Veja o erro

**Comportamento Esperado**
O que você esperava que acontecesse.

**Comportamento Atual**
O que realmente aconteceu.

**Código de Exemplo**
```php
// Código mínimo que reproduz o problema
Logger::info("Test", ['password' => 'secret']);
```

**Stack Trace (se aplicável)**
```
Erro completo aqui
```

**Ambiente**
- Versão do pacote: [ex: 1.0.9]
- PHP: [ex: 8.1.0]
- Monolog: [ex: 3.0.0]
- SO: [ex: Ubuntu 22.04]
- Servidor: [ex: Apache 2.4, Nginx 1.20]

**Informações Adicionais**
Qualquer outra informação relevante sobre o problema.
```

### Severidade

- **Crítica:** Sistema completamente inutilizável
- **Alta:** Funcionalidade principal quebrada
- **Média:** Funcionalidade secundária quebrada
- **Baixa:** Problema menor ou cosmético

## 💡 Solicitar Features

### Antes de Solicitar

1. Verifique se a feature já foi solicitada
2. Considere se a feature se alinha com os objetivos do projeto
3. Pense em como isso beneficiaria outros usuários

### Template de Feature Request

```markdown
**Problema que a Feature Resolve**
Uma descrição clara do problema que você está enfrentando.

**Solução Proposta**
Uma descrição clara da solução que você gostaria de ver.

**Alternativas Consideradas**
Outras soluções ou features que você considerou.

**Exemplo de Uso**
```php
// Como você gostaria de usar a nova feature
Logger::novaFuncionalidade();
```

**Impacto**
- [ ] Breaking change (requer mudanças em código existente)
- [ ] Não-breaking change (compatível com versões anteriores)

**Contexto Adicional**
Qualquer outra informação, contexto ou screenshots sobre a feature request.
```

### Critérios para Aceitação

- ✅ Alinha com os objetivos do projeto
- ✅ Beneficia múltiplos usuários
- ✅ Mantém compatibilidade quando possível
- ✅ Não aumenta significativamente a complexidade
- ✅ Tem casos de uso claros

## 🔍 Análise Estática e Qualidade

### PHPStan

O projeto usa PHPStan nível 7. Execute antes de commitar:

```bash
composer phpstan
```

### CodeSniffer

Verifique e corrija problemas de estilo:

```bash
# Verificar
composer cs-check

# Auto-corrigir
composer cs-fix
```

### Qualidade Completa

Execute todos os checks de uma vez:

```bash
composer quality
```

Isso executa:
1. CodeSniffer (PSR-12)
2. PHPStan (análise estática)
3. PHPUnit (testes)

## 📚 Recursos Úteis

### Documentação

- [README.md](../README.md) - Documentação principal
- [CHANGELOG.md](../CHANGELOG.md) - Histórico de mudanças
- [PUBLISH_GUIDE.md](../PUBLISH_GUIDE.md) - Guia de publicação

### Padrões e Convenções

- [PSR-12](https://www.php-fig.org/psr/psr-12/) - Extended Coding Style Guide
- [PSR-3](https://www.php-fig.org/psr/psr-3/) - Logger Interface
- [Conventional Commits](https://www.conventionalcommits.org/) - Padrão de commits
- [Semantic Versioning](https://semver.org/) - Versionamento semântico

### Ferramentas

- [PHPUnit](https://phpunit.de/) - Framework de testes
- [PHPStan](https://phpstan.org/) - Análise estática
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) - Verificação de código

## ❓ Dúvidas e Suporte

### Onde Perguntar?

- **GitHub Issues** - Para bugs e feature requests
- **GitHub Discussions** - Para dúvidas e discussões gerais
- **Email** - contato@codeflow.com.br (para questões privadas)

### Como Obter Ajuda?

1. **Procure primeiro** - Verifique issues e discussões existentes
2. **Seja específico** - Forneça contexto, código e mensagens de erro
3. **Seja paciente** - Mantenedores são voluntários e podem demorar para responder

## 🎯 Áreas que Precisam de Contribuição

Se você está procurando por onde começar, estas áreas sempre precisam de ajuda:

- 📝 **Documentação** - Melhorar exemplos, adicionar tutoriais
- 🧪 **Testes** - Aumentar cobertura, adicionar casos edge
- 🌐 **Internacionalização** - Traduções de documentação
- 🐛 **Bugs** - Verificar issues marcadas como "good first issue"
- ⚡ **Performance** - Otimizações e melhorias
- 🔒 **Segurança** - Revisões de segurança e melhorias

## 🙏 Agradecimentos

Obrigado por considerar contribuir para o CodeFlow Logger! Cada contribuição, grande ou pequena, é valiosa e ajuda a tornar este projeto melhor para todos.

### Tipos de Contribuição

- 💻 Código
- 📝 Documentação
- 🐛 Reportar bugs
- 💡 Sugerir features
- 🧪 Testes
- 📢 Divulgação
- 💬 Ajudar outros usuários

Todas são igualmente importantes e apreciadas!

---

**Desenvolvido com ❤️ pela comunidade CodeFlow Hub**