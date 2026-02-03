#!/bin/bash
# Carrega as variáveis do .env se existir
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

TOKEN="${TELEGRAM_BOT_TOKEN}"
CHAT_ID="${TELEGRAM_CHAT_ID}"
MESSAGE=$1

if [ -z "$TOKEN" ] || [ -z "$CHAT_ID" ]; then
    echo "Erro: Token ou Chat ID não configurados no .env"
    exit 1
fi

curl -s -X POST "https://api.telegram.org/bot$TOKEN/sendMessage" \
    -d "chat_id=$CHAT_ID" \
    -d "text=🚀 Tarefa Concluída!\n\n$MESSAGE" > /dev/null
