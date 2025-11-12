---
name: Bug Report
about: Reportar um bug no CodeFlow Logger
title: '[BUG] '
labels: 'bug'
assignees: ''
---

## 🐛 Descrição do Bug

Descrição clara e concisa do que está acontecendo de errado.

## 📋 Como Reproduzir

Passos para reproduzir o comportamento:

1. Vá para '...'
2. Clique em '....'
3. Role para baixo até '....'
4. Veja o erro

**Código de exemplo:**
```php
// Cole aqui o código que reproduz o problema
Logger::info("teste", ['data' => 'exemplo']);
```

## ✅ Comportamento Esperado

Descrição clara e concisa do que você esperava que acontecesse.

## 🖼️ Screenshots

Se aplicável, adicione screenshots para ajudar a explicar o problema.

## 🌐 Ambiente

**Sistema:**
- SO: [ex: Windows 11, Ubuntu 22.04]
- PHP: [ex: 8.1.2]
- Composer: [ex: 2.4.1]

**Dependências:**
- monolog/monolog: [versão]
- codeflow/logger: [versão]

**Configuração:**
```php
// Cole aqui sua configuração do Logger
Logger::enableLogByEmail('...', '...', '...');
```

## 📋 Contexto Adicional

Adicione qualquer contexto adicional sobre o problema aqui.

## ✅ Checklist

- [ ] Verifiquei que este bug não foi reportado anteriormente
- [ ] Incluí código de exemplo que reproduz o problema
- [ ] Incluí informações sobre o ambiente
- [ ] Li a documentação e não encontrei solução