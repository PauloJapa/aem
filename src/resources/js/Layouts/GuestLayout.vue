<template>
    <div class="guest-layout">
        <div class="guest-card">
            <slot />
        </div>
    </div>
</template>

<style>
.guest-layout {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #134e4a 0%, #0f766e 100%);
    padding: 1rem;
}

.guest-card {
    background: #ffffff;
    border-radius: 0.75rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 400px;
    padding: 2.5rem 2rem;
}
</style>
