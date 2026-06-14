<template>
    <div :style="estiloContainer">
        <img v-if="foto" :src="foto" :alt="nome" :style="estiloImg" />
        <span v-else :style="estiloIniciais">{{ iniciais }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    nome:    { type: String, required: true },
    foto:    { type: String, default: null },
    tamanho: { type: String, default: 'md' },
})

const px = computed(() => ({ sm: '28px', md: '36px', lg: '44px' }[props.tamanho] ?? '36px'))
const fs = computed(() => ({ sm: '11px', md: '13px', lg: '16px' }[props.tamanho] ?? '13px'))

const iniciais = computed(() => {
    const partes = props.nome.trim().split(/\s+/)
    if (partes.length === 1) return partes[0][0].toUpperCase()
    return (partes[0][0] + partes[partes.length - 1][0]).toUpperCase()
})

const estiloContainer = computed(() => ({
    width:          px.value,
    height:         px.value,
    borderRadius:   '50%',
    overflow:       'hidden',
    flexShrink:     0,
    display:        'flex',
    alignItems:     'center',
    justifyContent: 'center',
    background:     props.foto ? 'transparent' : '#0d9488',
}))

const estiloImg = computed(() => ({
    width:     '100%',
    height:    '100%',
    objectFit: 'cover',
}))

const estiloIniciais = computed(() => ({
    color:      '#fff',
    fontSize:   fs.value,
    fontWeight: '600',
    lineHeight: 1,
    userSelect: 'none',
}))
</script>
