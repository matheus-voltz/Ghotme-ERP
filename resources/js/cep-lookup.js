/**
 * CEP Lookup Integration (ViaCEP)
 * Preenche automaticamente campos de endereço ao digitar um CEP.
 */

'use strict';

$(function () {
    const cepLookup = $('.cep-lookup');

    if (cepLookup.length) {
        cepLookup.on('blur', function () {
            const cep = $(this).val().replace(/\D/g, '');
            const form = $(this).closest('form');

            if (cep.length === 8) {
                // Feedback visual de carregamento
                const cepInput = $(this);
                cepInput.addClass('disabled').prop('readonly', true);

                $.getJSON(`https://viacep.com.br/ws/${cep}/json/`, function (data) {
                    if (!data.erro) {
                        // Mapeamento Inteligente: Tenta encontrar os campos independente do nome
                        // Clientes usa: rua, bairro, cidade, estado
                        // Empresa usa: address, neighborhood, city, state
                        
                        const fields = {
                            logradouro: ['rua', 'address', 'logradouro'],
                            bairro: ['bairro', 'neighborhood'],
                            localidade: ['cidade', 'city', 'localidade'],
                            uf: ['estado', 'state', 'uf']
                        };

                        // Preenche Logradouro
                        fields.logradouro.forEach(name => {
                            const input = form.find(`[name="${name}"]`);
                            if (input.length) input.val(data.logradouro);
                        });

                        // Preenche Bairro
                        fields.bairro.forEach(name => {
                            const input = form.find(`[name="${name}"]`);
                            if (input.length) input.val(data.bairro);
                        });

                        // Preenche Cidade
                        fields.localidade.forEach(name => {
                            const input = form.find(`[name="${name}"]`);
                            if (input.length) input.val(data.localidade);
                        });

                        // Preenche Estado
                        fields.uf.forEach(name => {
                            const input = form.find(`[name="${name}"]`);
                            if (input.length) input.val(data.uf);
                        });

                        // Foca no campo de Número para agilizar o preenchimento
                        const numInput = form.find('[name="numero"], [name="number"]');
                        if (numInput.length) numInput.focus();

                    } else {
                        alert('CEP não encontrado.');
                    }
                }).always(function() {
                    cepInput.removeClass('disabled').prop('readonly', false);
                });
            }
        });
    }
});
