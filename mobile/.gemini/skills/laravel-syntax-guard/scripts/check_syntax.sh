#!/bin/bash

# Padrões a verificar
echo "### Laravel Syntax Guard: Verificação de Erros Comuns ###"

# 1. Verificar Auth:: em arquivos Blade (deve ser auth()->)
echo "--- Buscando por 'Auth::' em arquivos Blade ---"
grep -r "Auth::" --include="*.blade.php" ../resources/views | grep -v "vendor/"

# 2. Verificar encadeamentos de propriedades comuns sem operador de navegação segura (?->)
echo "--- Buscando por encadeamentos sem ?-> (Ex: ->company->) ---"
grep -rE "\->[a-zA-Z0-9_]+\->" --include="*.php" --include="*.blade.php" ../app ../resources/views | grep -vE "\?\->" | grep -v "vendor/"

# 3. Verificar acesso a propriedades de relacionamentos comuns sem proteção (Ex: ->client->name)
echo "--- Buscando por acessos a relacionamentos inseguros (client, user, company) ---"
grep -rE "\->(client|user|company|supplier)\->" --include="*.php" --include="*.blade.php" ../app ../resources/views | grep -vE "\?\->" | grep -v "vendor/"

# 4. Verificar acessos diretos a arrays/coleções sem fallback (Ex: [0]->menu)
echo "--- Buscando por acessos inseguros a arrays [0]-> em Blade ---"
grep -rE "\[0\]\->" --include="*.blade.php" ../resources/views | grep -vE "\?\?" | grep -v "vendor/"

# 5. Verificar arquivos @vite inexistentes (Evita ViteException)
echo "--- Verificando referências @vite em Blade ---"
VITE_FILES=$(grep -r "@vite(\[" --include="*.blade.php" ../resources/views | sed -n "s/.*'\(resources\/[^']*\)'.*/\1/p")
for FILE in $VITE_FILES; do
    if [ ! -f "../$FILE" ]; then
        echo "ALERTA: Arquivo Vite não encontrado: $FILE"
    fi
done

echo "--------------------------------------------------------"
echo "Recomendações:"
echo "1. Substitua 'Auth::user()' por 'auth()->user()' em arquivos Blade."
echo "2. Use o operador de navegação segura '?->' em cadeias de propriedades."
echo "3. Proteja acessos a relacionamentos como client, user e supplier com '?->'."
echo "4. Use fallback (?? []) ao iterar sobre arrays que podem ser nulos (ex: @foreach(\$item[0]->menu ?? [] ...))."
echo "5. Garanta que todos os arquivos dentro do helper @vite existem no sistema de arquivos."
