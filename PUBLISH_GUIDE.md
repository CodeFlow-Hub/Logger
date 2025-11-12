# 📦 Guia de Publicação no Packagist

Este documento explica como publicar o CodeFlow Logger no Packagist.

## ✅ Checklist Pré-Publicação

### 1. Verificar Qualidade do Código
```bash
composer install
composer quality  # Executa todos os testes e verificações
```

### 2. Verificar Arquivos Obrigatórios
- [x] `composer.json` - Configuração do pacote
- [x] `README.md` - Documentação principal  
- [x] `LICENSE` - Licença MIT
- [x] `CHANGELOG.md` - Histórico de mudanças
- [x] `src/Logger.php` - Classe principal
- [x] `src/helpers.php` - Função helper
- [x] Testes unitários em `tests/`

### 3. Validar composer.json
```bash
composer validate --strict
```

## 🚀 Publicação no Packagist

### Passo 1: Criar Repositório no GitHub

1. Criar repositório público: `https://github.com/codeflow-hub/logger`
2. Fazer push do código:

```bash
git init
git add .
git commit -m "feat: initial release v1.0.0"
git branch -M main
git remote add origin https://github.com/codeflow-hub/logger.git
git push -u origin main
```

### Passo 2: Criar Tag de Versão

```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

### Passo 3: Registrar no Packagist

1. Acessar https://packagist.org/
2. Login com GitHub
3. Clicar em "Submit"
4. Informar URL: `https://github.com/codeflow-hub/logger`
5. Clicar em "Check"
6. Confirmar submissão

### Passo 4: Configurar Auto-Update

1. No GitHub, acessar `Settings` → `Webhooks`
2. Adicionar webhook:
   - URL: `https://packagist.org/api/github`
   - Content type: `application/json`
   - Events: `Just the push event`

## 📋 Informações do Pacote

- **Nome:** `codeflow-hub/logger`
- **Namespace:** `CodeFlowHub\Logger`
- **Licença:** MIT
- **PHP mínimo:** 7.4
- **Dependência:** `monolog/monolog ^2.0|^3.0`

## 🏷️ Versionamento

Seguir [Semantic Versioning](https://semver.org/):

- **1.0.0** - Release inicial
- **1.0.1** - Patch (correções)
- **1.1.0** - Minor (novas features)
- **2.0.0** - Major (breaking changes)

### Para Nova Versão:
```bash
# Atualizar CHANGELOG.md
# Atualizar versão no composer.json se necessário
git add .
git commit -m "feat: add new feature"
git tag -a v1.1.0 -m "Release version 1.1.0"  
git push origin main
git push origin v1.1.0
```

## ✨ Recursos do Packagist

Após publicação, o pacote estará disponível:

- **Instalação:** `composer require codeflow-hub/logger`
- **URL:** https://packagist.org/packages/codeflow-hub/logger
- **Stats:** Downloads, estrelas, etc.
- **Auto-update:** Via webhook GitHub

## 📊 Badges para README

Adicionar ao README.md:

```markdown
[![Latest Stable Version](https://img.shields.io/packagist/v/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
[![Total Downloads](https://img.shields.io/packagist/dt/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
[![License](https://img.shields.io/packagist/l/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
[![PHP Version Require](https://img.shields.io/packagist/php-v/codeflow-hub/logger.svg)](https://packagist.org/packages/codeflow-hub/logger)
```

## 🎯 Próximos Passos

1. **Testar instalação:**
   ```bash
   composer create-project --no-dev temp-test
   cd temp-test  
   composer require codeflow-hub/logger
   ```

2. **Monitorar:**
   - Downloads no Packagist
   - Issues no GitHub
   - Feedback da comunidade

3. **Melhorar:**
   - Cobertura de testes
   - Documentação
   - Novas funcionalidades

## ❗ Troubleshooting

### Erro "Package not found"
- Verificar se o repositório é público
- Aguardar alguns minutos após submissão
- Verificar se composer.json é válido

### Erro de validação
```bash
composer validate --strict --no-check-all
```

### Auto-update não funciona
- Verificar webhook no GitHub
- Testar URL manualmente
- Verificar logs no Packagist

---

✅ **Seu pacote está pronto para ser publicado no Packagist!**