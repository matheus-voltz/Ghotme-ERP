---
name: laravel-syntax-guard
description: Validador de sintaxe Laravel. Use após cada modificação para garantir que 'Auth::' foi substituído por 'auth()->' em arquivos Blade, encadeamentos usem '?->' e que arquivos no @vite existam.
---

# Laravel Syntax Guard

Este agente é responsável por verificar se padrões comuns de erros no Laravel foram introduzidos durante as alterações no código.

## Quando Usar

Sempre execute esta verificação ao terminar qualquer alteração em arquivos Blade (.blade.php) ou PHP em geral no projeto Laravel.

## Workflow de Verificação

Execute o script de verificação integrado:

```bash
./scripts/check_syntax.sh
```

### Padrões Verificados:

1.  **Auth em Blade**: Substitua `Auth::user()` por `auth()->user()`.
2.  **Propriedades Nulas**: Use o operador de navegação segura `?->` em cadeias de objetos (ex: `user?->company?->niche`).
3.  **Vite Assets**: Valida se todos os arquivos referenciados no helper `@vite([...])` existem no diretório `resources/`.
