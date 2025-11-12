# Contribuindo para o CodeFlow Logger

Obrigado por considerar contribuir para o CodeFlow Logger! 

## 🚀 Como Contribuir

### 1. Fork e Clone

```bash
git clone https://github.com/seu-usuario/logger.git
cd logger
```

### 2. Instalar Dependências

```bash
composer install
```

### 3. Criar Branch

```bash
git checkout -b feature/sua-nova-feature
```

### 4. Desenvolver

- Siga os padrões PSR-12
- Adicione testes para novas funcionalidades
- Mantenha a documentação atualizada

### 5. Verificar Qualidade

```bash
# Executar todos os checks
composer quality

# Ou individualmente:
composer test
composer phpstan  
composer cs-check
```

### 6. Commit e Push

```bash
git add .
git commit -m "feat: adicionar nova funcionalidade"
git push origin feature/sua-nova-feature
```

### 7. Pull Request

Abra um PR com:
- Descrição clara das mudanças
- Testes que cobrem as alterações
- Atualização da documentação se necessário

## 📋 Diretrizes

### Code Style

- Usar PSR-12
- Indentação: 3 espaços 
- Linha máxima: 120 caracteres
- Documentação em PHPDoc

### Commits

Usar [Conventional Commits](https://conventionalcommits.org/):

- `feat:` Nova funcionalidade
- `fix:` Correção de bug
- `docs:` Documentação
- `style:` Formatação
- `refactor:` Refatoração
- `test:` Testes
- `chore:` Manutenção

### Testes

- Cobrir novas funcionalidades
- Manter cobertura > 80%
- Usar nomes descritivos

## 🐛 Reportar Bugs

Use o template de issue:

```markdown
**Descrição do Bug**
Descrição clara do que está acontecendo.

**Como Reproduzir**
Passos para reproduzir o comportamento.

**Comportamento Esperado** 
O que você esperava que acontecesse.

**Ambiente**
- PHP: [versão]
- Monolog: [versão] 
- SO: [sistema]
```

## 💡 Solicitar Features

Use o template de feature request:

```markdown
**Problema**
Que problema essa feature resolve?

**Solução Proposta**
Descreva a solução que você gostaria.

**Alternativas**
Alternativas que você considerou.

**Contexto Adicional**
Qualquer contexto adicional.
```

## ❓ Dúvidas

- GitHub Issues para dúvidas técnicas
- Email: contato@codeflow.com.br

Obrigado por contribuir! 🙏