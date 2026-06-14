import { ref, computed } from 'vue'
import { defineStore } from 'pinia'

export const useNotificacaoStore = defineStore('notificacao', () => {
    const notificacoes = ref([
        {
            id: 1,
            tipo: 'alerta',
            titulo: 'Estoque baixo',
            mensagem: 'Produto "Caneta Azul" abaixo do mínimo.',
            lida: false,
            criadoEm: new Date(Date.now() - 2 * 60 * 1000),
        },
        {
            id: 2,
            tipo: 'sucesso',
            titulo: 'Pedido aprovado',
            mensagem: 'Pedido #1042 foi aprovado com sucesso.',
            lida: false,
            criadoEm: new Date(Date.now() - 17 * 60 * 1000),
        },
        {
            id: 3,
            tipo: 'info',
            titulo: 'Conta a vencer',
            mensagem: 'Fatura Fornecedor X vence amanhã (R$ 1.240,00).',
            lida: false,
            criadoEm: new Date(Date.now() - 65 * 60 * 1000),
        },
    ])

    const naoLidas = computed(() => notificacoes.value.filter(n => !n.lida).length)

    function marcarComoLida(id) {
        const n = notificacoes.value.find(n => n.id === id)
        if (n) n.lida = true
    }

    function marcarTodasComoLidas() {
        notificacoes.value.forEach(n => (n.lida = true))
    }

    function adicionar(notificacao) {
        notificacoes.value.unshift({ id: Date.now(), lida: false, criadoEm: new Date(), ...notificacao })
    }

    function remover(id) {
        notificacoes.value = notificacoes.value.filter(n => n.id !== id)
    }

    return { notificacoes, naoLidas, marcarComoLida, marcarTodasComoLidas, adicionar, remover }
})
