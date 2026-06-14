import { ref, watch } from 'vue'
import { defineStore } from 'pinia'

export const useThemeStore = defineStore('theme', () => {
    const isDark = ref(
        localStorage.getItem('erp:dark') !== null
            ? localStorage.getItem('erp:dark') === 'true'
            : window.matchMedia('(prefers-color-scheme: dark)').matches
    )

    const sidebarCollapsed = ref(
        localStorage.getItem('erp:sidebar-collapsed') === 'true'
    )

    function init() {
        applyDark(isDark.value)
    }

    function toggleDark() {
        isDark.value = !isDark.value
    }

    function toggleSidebar() {
        sidebarCollapsed.value = !sidebarCollapsed.value
    }

    function applyDark(value) {
        document.documentElement.classList.toggle('dark', value)
    }

    watch(isDark, (value) => {
        applyDark(value)
        localStorage.setItem('erp:dark', String(value))
    })

    watch(sidebarCollapsed, (value) => {
        localStorage.setItem('erp:sidebar-collapsed', String(value))
    })

    return { isDark, sidebarCollapsed, init, toggleDark, toggleSidebar }
})
